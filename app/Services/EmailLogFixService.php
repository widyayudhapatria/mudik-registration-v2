<?php

namespace App\Services;

use App\Models\EmailLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmailLogFixService
{
    /**
     * Process Excel file containing emails to fix
     */
    public function processFile(UploadedFile $file): array
    {
        try {
            // Validate file
            if ($file->getSize() > 5 * 1024 * 1024) { // 5MB limit
                return [
                    'success' => false,
                    'error' => 'Ukuran file terlalu besar (max 5MB)'
                ];
            }

            if (!in_array($file->getClientOriginalExtension(), ['xlsx', 'xls', 'csv'])) {
                return [
                    'success' => false,
                    'error' => 'Format file harus .xlsx, .xls, atau .csv'
                ];
            }

            // Parse Excel
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $this->parseWorksheet($worksheet);

            if (empty($rows)) {
                return [
                    'success' => false,
                    'error' => 'File Excel kosong atau tidak ada data'
                ];
            }

            // Validate headers - expecting at least "email" column
            if (!isset($rows[0]['email'])) {
                return [
                    'success' => false,
                    'error' => 'File Excel harus memiliki kolom "email"'
                ];
            }

            // Extract unique emails
            $emails = array_unique(array_filter(array_map(function ($row) {
                return trim(strtolower($row['email'] ?? ''));
            }, $rows)));

            if (empty($emails)) {
                return [
                    'success' => false,
                    'error' => 'Tidak ada email yang valid ditemukan'
                ];
            }

            // Find latest email log status for each email
            $emailStatuses = $this->getEmailStatuses($emails);

            return [
                'success' => true,
                'filename' => $file->getClientOriginalName(),
                'total_emails' => count($emails),
                'email_statuses' => $emailStatuses,
                'emails' => array_values($emails),
            ];
        } catch (\Exception $e) {
            Log::error('EmailLogFixService: Error processing file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parse worksheet into array of rows
     */
    protected function parseWorksheet($worksheet): array
    {
        $rows = [];
        $headers = [];
        $firstRow = true;

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            $colIndex = 0;

            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();

                if ($firstRow) {
                    // Normalize header names
                    $header = strtolower(trim($value));
                    $headers[$colIndex] = $header;
                } else {
                    $header = $headers[$colIndex] ?? 'col_' . $colIndex;
                    $rowData[$header] = $value;
                }

                $colIndex++;
            }

            if ($firstRow) {
                $firstRow = false;
            } else {
                // Skip empty rows
                if (!empty(array_filter($rowData))) {
                    $rows[] = $rowData;
                }
            }
        }

        return $rows;
    }

    /**
     * Get email statuses from database
     */
    protected function getEmailStatuses(array $emails): array
    {
        $statuses = [];

        foreach ($emails as $email) {
            // Get latest email log for this email with form_link relation
            $latestLog = EmailLog::with('formLink')
                ->where('email_to', $email)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            // Check form_link status
            $formLinkStatus = $latestLog && $latestLog->formLink
                ? $latestLog->formLink->status
                : null;

            // Can fix only if:
            // 1. Email log status is 'sent'
            // 2. Form link exists and status is 'pending'
            $canFix = $latestLog
                && $latestLog->status === 'sent'
                && $formLinkStatus === 'pending';

            $statuses[] = [
                'email' => $email,
                'has_log' => $latestLog !== null,
                'latest_status' => $latestLog?->status ?? 'no_log',
                'email_log_id' => $latestLog?->id,
                'form_link_id' => $latestLog?->form_link_id,
                'form_link_status' => $formLinkStatus ?? 'no_form_link',
                'sent_at' => $latestLog?->sent_at?->format('Y-m-d H:i:s'),
                'created_at' => $latestLog?->created_at?->format('Y-m-d H:i:s'),
                'can_fix' => $canFix,
                'skip_reason' => !$canFix ? $this->getSkipReason($latestLog, $formLinkStatus) : null,
            ];
        }

        return $statuses;
    }

    /**
     * Get reason why email cannot be fixed
     */
    protected function getSkipReason($emailLog, $formLinkStatus): string
    {
        if (!$emailLog) {
            return 'No email log found';
        }

        if ($emailLog->status !== 'sent') {
            return 'Email log status is not SENT (current: ' . strtoupper($emailLog->status) . ')';
        }

        if (!$formLinkStatus || $formLinkStatus === 'no_form_link') {
            return 'No form link associated';
        }

        if ($formLinkStatus !== 'pending') {
            return 'Form link status is not PENDING (current: ' . strtoupper($formLinkStatus) . ')';
        }

        return 'Unknown reason';
    }

    /**
     * Process email log fixes
     */
    public function processEmailFixes(array $emails): array
    {
        $results = [
            'total' => count($emails),
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($emails as $email) {
                $email = trim(strtolower($email));

                // Get latest email log with status 'sent' and eager load form_link
                $latestLog = EmailLog::with('formLink')
                    ->where('email_to', $email)
                    ->where('status', 'sent')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$latestLog) {
                    $results['skipped']++;
                    $results['details'][] = [
                        'email' => $email,
                        'status' => 'skipped',
                        'reason' => 'No sent email log found',
                    ];
                    continue;
                }

                // Check if form_link exists and status is pending
                if (!$latestLog->formLink) {
                    $results['skipped']++;
                    $results['details'][] = [
                        'email' => $email,
                        'status' => 'skipped',
                        'reason' => 'No form link associated with this email log',
                        'email_log_id' => $latestLog->id,
                    ];
                    continue;
                }

                if ($latestLog->formLink->status !== 'pending') {
                    $results['skipped']++;
                    $results['details'][] = [
                        'email' => $email,
                        'status' => 'skipped',
                        'reason' => 'Form link status is not PENDING (current: ' . strtoupper($latestLog->formLink->status) . ')',
                        'email_log_id' => $latestLog->id,
                        'form_link_id' => $latestLog->form_link_id,
                        'form_link_status' => $latestLog->formLink->status,
                    ];
                    continue;
                }

                // Store sent_at value
                $originalSentAt = $latestLog->sent_at;

                // Update the email log
                $latestLog->status = 'failed';
                $latestLog->sent_at = null;
                $latestLog->failed_at = $originalSentAt;
                $latestLog->error_message = 'SMTP limit reached - manually marked as failed';

                if ($latestLog->save()) {
                    $results['updated']++;
                    $results['details'][] = [
                        'email' => $email,
                        'status' => 'updated',
                        'email_log_id' => $latestLog->id,
                        'original_sent_at' => $originalSentAt?->format('Y-m-d H:i:s'),
                        'new_failed_at' => $latestLog->failed_at?->format('Y-m-d H:i:s'),
                    ];
                } else {
                    $results['errors']++;
                    $results['details'][] = [
                        'email' => $email,
                        'status' => 'error',
                        'reason' => 'Failed to save email log',
                    ];
                }
            }

            DB::commit();

            Log::info('EmailLogFixService: Processed email log fixes', [
                'total' => $results['total'],
                'updated' => $results['updated'],
                'skipped' => $results['skipped'],
                'errors' => $results['errors'],
            ]);

            return [
                'success' => true,
                'results' => $results,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('EmailLogFixService: Error processing email fixes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Terjadi kesalahan saat memproses: ' . $e->getMessage(),
                'results' => $results,
            ];
        }
    }

    /**
     * Generate Excel template for email import
     */
    public function generateTemplate(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'email');

        // Add sample data
        $sheet->setCellValue('A2', 'example1@email.com');
        $sheet->setCellValue('A3', 'example2@email.com');

        // Style headers
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setAutoSize(true);

        // Save to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'email_log_fix_template_');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempFile);

        return $tempFile;
    }
}
