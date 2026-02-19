<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make qr_code_id nullable to support logging failures when QR not found
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite requires rebuilding the table to change nullability
            DB::statement('PRAGMA foreign_keys=OFF');
            DB::statement('CREATE TABLE scan_logs_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                qr_code_id BIGINT UNSIGNED,
                admin_id BIGINT UNSIGNED NOT NULL,
                scan_result VARCHAR(255) NOT NULL,
                failure_reason VARCHAR(255),
                ip_address VARCHAR(45),
                user_agent TEXT,
                scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (qr_code_id) REFERENCES qr_codes(id) ON DELETE CASCADE,
                FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
            )');
            DB::statement('INSERT INTO scan_logs_new SELECT * FROM scan_logs');
            DB::statement('DROP TABLE scan_logs');
            DB::statement('ALTER TABLE scan_logs_new RENAME TO scan_logs');
            DB::statement('CREATE INDEX scan_logs_qr_code_id_index ON scan_logs(qr_code_id)');
            DB::statement('CREATE INDEX scan_logs_admin_id_index ON scan_logs(admin_id)');
            DB::statement('CREATE INDEX scan_logs_scanned_at_index ON scan_logs(scanned_at)');
            DB::statement('CREATE INDEX scan_logs_scan_result_index ON scan_logs(scan_result)');
            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            // MySQL/MariaDB
            DB::statement("ALTER TABLE `scan_logs` MODIFY `qr_code_id` BIGINT UNSIGNED NULL;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');
            DB::statement('CREATE TABLE scan_logs_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                qr_code_id BIGINT UNSIGNED NOT NULL,
                admin_id BIGINT UNSIGNED NOT NULL,
                scan_result VARCHAR(255) NOT NULL,
                failure_reason VARCHAR(255),
                ip_address VARCHAR(45),
                user_agent TEXT,
                scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (qr_code_id) REFERENCES qr_codes(id) ON DELETE CASCADE,
                FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
            )');
            DB::statement('INSERT INTO scan_logs_new SELECT * FROM scan_logs');
            DB::statement('DROP TABLE scan_logs');
            DB::statement('ALTER TABLE scan_logs_new RENAME TO scan_logs');
            DB::statement('CREATE INDEX scan_logs_qr_code_id_index ON scan_logs(qr_code_id)');
            DB::statement('CREATE INDEX scan_logs_admin_id_index ON scan_logs(admin_id)');
            DB::statement('CREATE INDEX scan_logs_scanned_at_index ON scan_logs(scanned_at)');
            DB::statement('CREATE INDEX scan_logs_scan_result_index ON scan_logs(scan_result)');
            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            DB::statement("ALTER TABLE `scan_logs` MODIFY `qr_code_id` BIGINT UNSIGNED NOT NULL;");
        }
    }
};
