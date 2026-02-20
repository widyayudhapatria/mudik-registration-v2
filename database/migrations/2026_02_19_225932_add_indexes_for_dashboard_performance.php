<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to add indexes with IF NOT EXISTS for idempotency
        DB::statement('ALTER TABLE registrations ADD INDEX IF NOT EXISTS idx_registrations_destination (destination_id)');
        DB::statement('ALTER TABLE registrations ADD INDEX IF NOT EXISTS idx_registrations_created_at (created_at)');
        DB::statement('ALTER TABLE registrations ADD INDEX IF NOT EXISTS idx_registrations_approved_at (approved_at)');
        DB::statement('ALTER TABLE registrations ADD INDEX IF NOT EXISTS idx_registrations_dest_created (destination_id, created_at)');

        DB::statement('ALTER TABLE participants ADD INDEX IF NOT EXISTS idx_participants_registration (registration_id)');

        DB::statement('ALTER TABLE form_links ADD INDEX IF NOT EXISTS idx_form_links_status (status)');
        DB::statement('ALTER TABLE form_links ADD INDEX IF NOT EXISTS idx_form_links_created_at (created_at)');

        DB::statement('ALTER TABLE destinations ADD INDEX IF NOT EXISTS idx_destinations_display_order (display_order)');
        DB::statement('ALTER TABLE destinations ADD INDEX IF NOT EXISTS idx_destinations_is_active (is_active)');
        DB::statement('ALTER TABLE destinations ADD INDEX IF NOT EXISTS idx_destinations_active_order (is_active, display_order)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Use raw SQL to drop indexes with IF EXISTS for idempotency
        DB::statement('ALTER TABLE registrations DROP INDEX IF EXISTS idx_registrations_destination');
        DB::statement('ALTER TABLE registrations DROP INDEX IF EXISTS idx_registrations_created_at');
        DB::statement('ALTER TABLE registrations DROP INDEX IF EXISTS idx_registrations_approved_at');
        DB::statement('ALTER TABLE registrations DROP INDEX IF EXISTS idx_registrations_dest_created');

        DB::statement('ALTER TABLE participants DROP INDEX IF EXISTS idx_participants_registration');

        DB::statement('ALTER TABLE form_links DROP INDEX IF EXISTS idx_form_links_status');
        DB::statement('ALTER TABLE form_links DROP INDEX IF EXISTS idx_form_links_created_at');

        DB::statement('ALTER TABLE destinations DROP INDEX IF EXISTS idx_destinations_display_order');
        DB::statement('ALTER TABLE destinations DROP INDEX IF EXISTS idx_destinations_is_active');
        DB::statement('ALTER TABLE destinations DROP INDEX IF EXISTS idx_destinations_active_order');
    }
};
