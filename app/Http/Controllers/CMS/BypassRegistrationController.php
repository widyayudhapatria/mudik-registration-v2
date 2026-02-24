<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Registration;
use App\Models\RegistrationImport;
use App\Services\BypassRegistrationService;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BypassRegistrationController extends Controller
{
    protected ExcelImportService $excelService;
    protected BypassRegistrationService $bypassService;

    public function __construct(
        ExcelImportService $excelService,
        BypassRegistrationService $bypassService
    ) {
        $this->excelService = $excelService;
        $this->bypassService = $bypassService;

        $this->middleware(function ($request, $next) {
            if (!auth('admin')->user()->isSuperAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Show import form
     */
    public function showForm()
    {
        $destinations = Destination::where('is_active', true)->get();

        return view('cms.bypass-registration.form', [
            'destinations' => $destinations
        ]);
    }

    /**
     * Handle file upload & validation
     */
    public function validateFile(Request $request)
    {
        $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB
        ]);

        $destination = Destination::findOrFail($request->destination_id);
        $file = $request->file('file');

        // Process and validate Excel
        $result = $this->excelService->processFile($file, $destination);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Validasi gagal',
                'validation_errors' => $result['validation_errors'] ?? [],
                'error_count' => $result['error_count'] ?? 0,
            ], 422);
        }

        // Store in session for confirmation
        session()->put('bypass_import_data', [
            'destination_id' => $destination->id,
            'destination_name' => $destination->name,
            'filename' => $result['filename'],
            'registrations' => $result['registrations'],
            'total_registrations' => $result['total_registrations'],
            'total_participants' => $result['total_participants'],  // All participants (including under 4)
            'total_participants_for_quota' => $result['total_participants_for_quota'],  // Only adults
            'total_under_4' => $result['total_under_4'],
            'warnings' => $result['warnings'] ?? [],
        ]);

        // Calculate quota impact (using only adult count)
        $totalQuota = $destination->total_quota;
        $usedQuota = DB::table('participants')
            ->join('registrations', 'participants.registration_id', '=', 'registrations.id')
            ->where('registrations.destination_id', $destination->id)
            ->where('registrations.approved_at', '!=', null)
            ->where('participants.is_child_under_4', false)  // Only count adults
            ->count();

        $remainingQuota = $totalQuota - $usedQuota;
        $wouldUse = $result['total_participants_for_quota'];  // Only adult count for quota
        $afterImport = $usedQuota + $wouldUse;

        return response()->json([
            'success' => true,
            'preview' => [
                'total_registrations' => $result['total_registrations'],
                'total_participants' => $result['total_participants'],  // All
                'total_participants_for_quota' => $result['total_participants_for_quota'],  // Adults only
                'total_under_4' => $result['total_under_4'],
                'warnings' => $result['warnings'],
            ],
            'quota' => [
                'total' => $totalQuota,
                'used' => $usedQuota,
                'remaining' => $remainingQuota,
                'would_use' => $wouldUse,
                'after_import' => $afterImport,
            ],
            'registrations' => array_slice($result['registrations'], 0, 5), // First 5 for preview
            'has_more' => count($result['registrations']) > 5,
        ]);
    }

    /**
     * Show preview & confirmation
     */
    public function showPreview()
    {
        $importData = session('bypass_import_data');

        if (!$importData) {
            return redirect()->route('cms.bypass-registrations.form')
                ->with('error', 'Session expired, silahkan upload ulang.');
        }

        $destination = Destination::findOrFail($importData['destination_id']);

        return view('cms.bypass-registration.preview', [
            'importData' => $importData,
            'destination' => $destination,
        ]);
    }

    /**
     * Confirm & process import
     */
    public function confirmImport(Request $request)
    {
        $request->validate([
            'confirm' => 'required|accepted'
        ]);

        $importData = session('bypass_import_data');

        if (!$importData) {
            return response()->json([
                'success' => false,
                'error' => 'Session expired'
            ], 422);
        }

        $destination = Destination::findOrFail($importData['destination_id']);
        $admin = auth('admin')->user();

        // Process import
        $result = $this->bypassService->importRegistrations(
            $importData['registrations'],
            $destination,
            $admin,
            $importData['filename']
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error']
            ], 422);
        }

        session()->forget('bypass_import_data');

        return response()->json([
            'success' => true,
            'import_id' => $result['import_id'],
            'successful' => $result['successful'],
            'failed' => $result['failed'],
            'message' => $result['message'],
            'redirect' => route('cms.bypass-registrations.results', $result['import_id'])
        ]);
    }

    /**
     * Show import results
     */
    public function showResults($importId)
    {
        $import = RegistrationImport::findOrFail($importId);

        // Authorization check
        if ($import->admin_id !== auth('admin')->id()) {
            $this->authorize('view-all-imports');
        }

        $registrations = Registration::where('is_bypass', true)
            ->where('destination_id', $import->destination_id)
            ->orderBy('created_at', 'desc')
            ->take($import->total_registrations)
            ->get()
            ->map(function ($registration) {
                // Calculate adult count (age >= 4)
                $adultCount = $registration->participants()
                    ->where('is_child_under_4', false)
                    ->count();

                // Calculate under 4 count
                $under4Count = $registration->participants()
                    ->where('is_child_under_4', true)
                    ->count();

                // Add to registration object
                $registration->adult_count = $adultCount;
                $registration->under_4_count = $under4Count;

                return $registration;
            });

        return view('cms.bypass-registration.results', [
            'import' => $import,
            'registrations' => $registrations,
        ]);
    }

    /**
     * Show import history
     */
    public function history()
    {
        $imports = RegistrationImport::with(['admin', 'destination'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('cms.bypass-registration.history', [
            'imports' => $imports,
        ]);
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $filePath = storage_path('templates/bypass-registration-template.xlsx');

        if (!file_exists($filePath)) {
            $this->createTemplate($filePath);
        }

        return response()->download($filePath, 'bypass-registration-template.xlsx');
    }

    /**
     * Create Excel template file
     */
    protected function createTemplate(string $filePath)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bypass Registrations');

        // Set headers
        $headers = [
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

        // Style for header (bold font)
        $headerFont = new \PhpOffice\PhpSpreadsheet\Style\Font();
        $headerFont->setBold(true);

        // Add headers with styling
        foreach ($headers as $col => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->setCellValue($colLetter . '1', $header);

            $style = $sheet->getStyle($colLetter . '1');
            $style->setFont($headerFont);
        }

        // Add sample data (3 complete families as per template spec)
        $sampleData = [
            // Family 1: Ahmad Rasimun (3 members)
            ['Ahmad Rasimun', 'ahmad.rasimun@email.com', '3500123456789012', '1990-05-15', 3, '3500789012345678', 'Ahmad Rasimun', '3500123456789012', '1990-05-15', 'false'],
            ['Ahmad Rasimun', 'ahmad.rasimun@email.com', '3500123456789012', '1990-05-15', 3, '3500789012345678', 'Siti Umi Ahmad', '3500123456789013', '1992-08-20', 'false'],
            ['Ahmad Rasimun', 'ahmad.rasimun@email.com', '3500123456789012', '1990-05-15', 3, '3500789012345678', 'Joni Rasimun', '3500123456789014', '2020-12-10', 'true'],

            // Family 2: Budi Hartono (2 members)
            ['Budi Hartono', 'budi.hartono@email.com', '3500987654321098', '1985-03-22', 2, '3500111222333444', 'Budi Hartono', '3500987654321098', '1985-03-22', 'false'],
            ['Budi Hartono', 'budi.hartono@email.com', '3500987654321098', '1985-03-22', 2, '3500111222333444', 'Doni Hartono', '3500987654321099', '2018-07-01', 'false'],

            // Family 3: Siti Nurhaliza (4 members)
            ['Siti Nurhaliza', 'siti.nurhaliza@email.com', '3500456789012345', '1988-11-08', 4, '3500555666777888', 'Siti Nurhaliza', '3500456789012345', '1988-11-08', 'false'],
            ['Siti Nurhaliza', 'siti.nurhaliza@email.com', '3500456789012345', '1988-11-08', 4, '3500555666777888', 'Hafiz Nur', '3500456789012346', '2015-02-14', 'false'],
            ['Siti Nurhaliza', 'siti.nurhaliza@email.com', '3500456789012345', '1988-11-08', 4, '3500555666777888', 'Lala Nur', '3500456789012347', '2017-09-30', 'false'],
            ['Siti Nurhaliza', 'siti.nurhaliza@email.com', '3500456789012345', '1988-11-08', 4, '3500555666777888', 'Mika Nur', '3500456789012348', '2022-05-20', 'true'],
        ];

        // Add sample data rows
        foreach ($sampleData as $rowIdx => $rowData) {
            foreach ($rowData as $col => $value) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                $sheet->setCellValue($colLetter . ($rowIdx + 2), $value);
            }
        }

        // Auto-adjust columns
        foreach ($headers as $col => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Set header row height
        $sheet->getRowDimension('1')->setRowHeight(25);

        // Freeze header row
        $sheet->freezePane('A2');

        // Save
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);
    }
}
