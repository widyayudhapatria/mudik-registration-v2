<?php

namespace App\Console\Commands;

use App\Models\QrCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RenameQrCodeFiles extends Command
{
    protected $signature = 'qrcode:rename-files';

    public function handle(): void
    {
        $qrCodes = QrCode::all();
        $renamed = 0;
        $skipped = 0;

        foreach ($qrCodes as $qrCode) {
            $oldPath = "qr-codes/{$qrCode->id}.png";
            $newPath = "qr-codes/{$qrCode->token_qr}.png";

            if (!Storage::exists($oldPath)) {
                $skipped++;
                continue;
            }

            Storage::move($oldPath, $newPath);
            $this->info("Renamed: {$oldPath} → {$newPath}");
            $renamed++;
        }

        $this->info("Done. Renamed: {$renamed}, Skipped: {$skipped}");
    }
}