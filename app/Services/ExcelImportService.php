<?php

namespace App\Services;

use App\Models\Destination;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportService
{
    protected array $validationReport = [];
    protected array $parsedData = [];

    /**
     * Process and validate Excel file
     */
    public function processFile(UploadedFile $file, Destination $destination): array
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

            // Validate headers
            $headerValidation = $this->validateHeaders(array_keys($rows[0]));
            if (!$headerValidation['valid']) {
                return [
                    'success' => false,
                    'error' => $headerValidation['message']
                ];
            }

            // Validate data
            $validator = new BypassRegistrationValidator();
            $validation = $validator->validate($rows);

            if (!$validation['valid'] && $validation['error_count'] > 0) {
                return [
                    'success' => false,
                    'validation_errors' => $validation['errors'],
                    'error_count' => $validation['error_count'],
                    'warning_count' => $validation['warning_count'],
                ];
            }

            // Group data by representative
            $groupedData = $this->groupByRepresentativeNik($rows);
            $maxPerFamily = 10;
            $earlyErrors = [];

            foreach ($groupedData as $nik => $group) {
                $participantCount = count($group['participants']);
                if ($participantCount > $maxPerFamily) {
                    $earlyErrors[] = [
                        'row' => '-', // tidak tersedia row tunggal karena sudah digroup
                        'type' => 'family_count',
                        'message' => "Perwakilan {$group['representative']['name']} (NIK: {$nik}) mendaftarkan {$participantCount} peserta, melebihi batas maksimum per keluarga ({$maxPerFamily})."
                    ];
                }
            }

            if (!empty($earlyErrors)) {
                return [
                    'success' => false,
                    'validation_errors' => $earlyErrors,
                    'error_count' => count($earlyErrors),
                    'warning_count' => 0,
                ];
            }

            $registrationData = $this->transformToRegistrations($groupedData);

            // Calculate total counts
            $totalParticipantsAll = array_sum(array_map(fn($r) => count($r['participants']), $registrationData));
            $totalParticipantsAdult = array_sum(array_map(fn($r) => $r['adult_count'], $registrationData));
            $totalParticipantsUnder4 = array_sum(array_map(fn($r) => $r['under_4_count'], $registrationData));

            return [
                'success' => true,
                'registrations' => $registrationData,
                'total_registrations' => count($registrationData),
                'total_participants' => $totalParticipantsAll,  // All participants (including under 4)
                'total_participants_for_quota' => $totalParticipantsAdult,  // Only adults (age >= 4) for quota
                'total_under_4' => $totalParticipantsUnder4,
                'warnings' => $validation['warnings'] ?? [],
                'filename' => $file->getClientOriginalName(),
            ];
        } catch (\Exception $e) {
            Log::error('Excel import error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Gagal membaca file Excel: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parse worksheet into array
     */
    protected function parseWorksheet($worksheet): array
    {
        $rows = [];
        $highestRow = $worksheet->getHighestRow();
        $highestCol = $worksheet->getHighestColumn();

        // Get headers from first row
        $headers = [];
        for ($col = 'A'; $col !== chr(ord($highestCol) + 1); $col++) {
            $value = $worksheet->getCell($col . '1')->getValue();
            if ($value) {
                $headers[$col] = strtolower(trim($value));
            }
        }

        // Parse data rows
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [];
            $hasData = false;

            foreach ($headers as $col => $header) {
                $cellValue = $worksheet->getCell($col . $row)->getValue();

                // Convert Excel serial dates to YYYY-MM-DD format
                if (in_array($header, ['representative_birth_date', 'participant_birth_date'])) {
                    $cellValue = $this->convertExcelDateToString($cellValue);
                }

                $rowData[$header] = $cellValue;
                if (!empty($cellValue)) {
                    $hasData = true;
                }
            }

            if ($hasData) {
                $rows[] = $rowData;
            }
        }

        return $rows;
    }

    /**
     * Convert Excel serial date to YYYY-MM-DD string
     */
    protected function convertExcelDateToString($value): string
    {
        if (empty($value)) {
            return '';
        }

        // If already a string in YYYY-MM-DD format, return as-is
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return trim($value);
        }

        // If numeric (Excel serial), convert
        if (is_numeric($value)) {
            try {
                if (class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);
                    if ($dt instanceof \DateTimeInterface) {
                        return $dt->format('Y-m-d');
                    }
                }
            } catch (\Throwable $e) {
                return '';
            }
        }

        // If string but not YYYY-MM-DD, try to parse with strtotime
        if (is_string($value)) {
            $ts = @strtotime(trim($value));
            if ($ts !== false && $ts > 0) {
                return date('Y-m-d', $ts);
            }
        }

        return '';
    }

    /**
     * Validate Excel headers
     */
    protected function validateHeaders(array $headers): array
    {
        $requiredHeaders = [
            'representative_name',
            'representative_email',
            'representative_nik',
            'representative_birth_date',
            'family_count',
            'kk_number',
            'participant_full_name',
            'participant_nik',
            'participant_birth_date',
            'is_child_under_4',
        ];

        $missing = array_diff($requiredHeaders, $headers);

        if (!empty($missing)) {
            return [
                'valid' => false,
                'message' => 'Header Excel tidak lengkap. Kolom yang hilang: ' . implode(', ', $missing)
            ];
        }

        return ['valid' => true];
    }

    /**
     * Group rows by representative NIK
     */
    protected function groupByRepresentativeNik(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $nik = (string)$row['representative_nik'];
            if (!isset($grouped[$nik])) {
                $grouped[$nik] = [
                    'representative' => [
                        'name' => $row['representative_name'],
                        'email' => $row['representative_email'],
                        'nik' => $nik,
                        'birth_date' => $row['representative_birth_date'],
                        'family_count' => (int)$row['family_count'],
                        'kk_number' => $row['kk_number'],
                    ],
                    'participants' => []
                ];
            }

            $grouped[$nik]['participants'][] = [
                'full_name' => $row['participant_full_name'],
                'nik_kia' => $row['participant_nik'],
                'birth_date' => $row['participant_birth_date'],
                'is_child_under_4' => $this->parseBoolean($row['is_child_under_4']),
            ];
        }

        return $grouped;
    }

    /**
     * Transform grouped data to registrations format
     * Also calculates adult count (age >= 4) for quota purposes
     */
    protected function transformToRegistrations(array $grouped): array
    {
        $registrations = [];

        foreach ($grouped as $nik => $group) {
            // Calculate adult count (participants aged >= 4 years old)
            $adultCount = 0;
            $under4Count = 0;

            foreach ($group['participants'] as $participant) {
                if ($this->isAdult($participant['birth_date'])) {
                    $adultCount++;
                } else {
                    $under4Count++;
                }
            }

            $registrations[] = [
                'representative_name' => $group['representative']['name'],
                'representative_email' => $group['representative']['email'],
                'representative_nik' => $group['representative']['nik'],
                'representative_birth_date' => $group['representative']['birth_date'],
                'family_count' => $group['representative']['family_count'],
                'kk_number' => $group['representative']['kk_number'],
                'participants' => $group['participants'],
                'adult_count' => $adultCount,
                'under_4_count' => $under4Count,
                'total_for_quota' => $adultCount, // Only adults count for quota
            ];
        }

        return $registrations;
    }

    /**
     * Check if participant is adult (age >= 4 years old)
     */
    protected function isAdult(string $birthDate): bool
    {
        if (empty($birthDate)) {
            return true; // Default to adult if no birth date
        }

        try {
            $dob = new \DateTime($birthDate);
            $today = new \DateTime();
            $age = $today->diff($dob)->y;
            return $age >= 4;
        } catch (\Exception $e) {
            return true; // Default to adult if date parsing fails
        }
    }

    /**
     * Parse boolean from various formats
     */
    protected function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $strValue = strtolower(trim((string)$value));
        return in_array($strValue, ['true', '1', 'yes', 'y']);
    }
}
