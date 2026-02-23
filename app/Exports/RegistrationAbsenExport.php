<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistrationAbsenExport
{
    public function __construct(
        protected Collection $registrations
    ) {}

    public function download(): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Absen Peserta Mudik');

        // Header
        $headers = ['No', 'Nama Peserta', 'NIK / KIA', 'Tujuan', 'Keterangan', 'Bus', 'Nomor Kursi', 'Tanda Tangan'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(65 + $i) . '1', $header);
        }

        // Header style
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Column widths
        foreach (['A' => 6, 'B' => 35, 'C' => 22, 'D' => 20, 'E' => 15, 'F' => 12, 'G' => 14, 'H' => 30] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Data
        $row = 2;
        $no = 1;
        foreach ($this->registrations as $registration) {
            $seatsByParticipant = $registration->seatAllocations->keyBy('participant_id');
            $isScanned = $registration->qrCode?->isScanned() ?? false;

            foreach ($registration->participants as $participant) {
                $seat = $seatsByParticipant->get($participant->id);
                $isLap = $participant->is_child_under_4;

                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $participant->full_name);
                $sheet->setCellValue('C' . $row, $participant->nik_kia);
                $sheet->setCellValue('D' . $row, $registration->destination->name);
                $sheet->setCellValue('E' . $row, $isLap ? 'Dipangku' : '');
                $sheet->setCellValue('F' . $row, ($isScanned && $seat && !$seat->isNoSeat()) ? $seat->bus_name : '-');
                $sheet->setCellValue('G' . $row, ($isScanned && $seat && !$seat->isNoSeat()) ? $seat->seat_label : '-');
                $sheet->setCellValue('H' . $row, '');
                $sheet->getRowDimension($row)->setRowHeight(35);

                $row++;
            }
        }

        // Border
        if ($row > 2) {
            $sheet->getStyle('A1:H' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                ],
            ]);
        }

        // Save & download
        $filename = 'absen-mudik-' . now()->format('Ymd-His') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}