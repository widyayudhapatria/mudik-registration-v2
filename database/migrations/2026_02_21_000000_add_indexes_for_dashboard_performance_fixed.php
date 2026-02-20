<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fixed version that works with MySQL < 8.0.13
     */
    public function up(): void
    {
        // Helper function to safely add index
        $this->addIndexSafely('registrations', 'idx_registrations_destination', ['destination_id']);
        $this->addIndexSafely('registrations', 'idx_registrations_created_at', ['created_at']);
        $this->addIndexSafely('registrations', 'idx_registrations_approved_at', ['approved_at']);
        $this->addIndexSafely('registrations', 'idx_registrations_dest_created', ['destination_id', 'created_at']);

        $this->addIndexSafely('participants', 'idx_participants_registration', ['registration_id']);

        $this->addIndexSafely('form_links', 'idx_form_links_status', ['status']);
        $this->addIndexSafely('form_links', 'idx_form_links_created_at', ['created_at']);

        $this->addIndexSafely('destinations', 'idx_destinations_display_order', ['display_order']);
        $this->addIndexSafely('destinations', 'idx_destinations_is_active', ['is_active']);
        $this->addIndexSafely('destinations', 'idx_destinations_active_order', ['is_active', 'display_order']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Helper function to safely drop index
        $this->dropIndexSafely('registrations', 'idx_registrations_destination');
        $this->dropIndexSafely('registrations', 'idx_registrations_created_at');
        $this->dropIndexSafely('registrations', 'idx_registrations_approved_at');
        $this->dropIndexSafely('registrations', 'idx_registrations_dest_created');

        $this->dropIndexSafely('participants', 'idx_participants_registration');

        $this->dropIndexSafely('form_links', 'idx_form_links_status');
        $this->dropIndexSafely('form_links', 'idx_form_links_created_at');

        $this->dropIndexSafely('destinations', 'idx_destinations_display_order');
        $this->dropIndexSafely('destinations', 'idx_destinations_is_active');
        $this->dropIndexSafely('destinations', 'idx_destinations_active_order');
    }

    /**
     * Safely add an index to a table
     * Handles MySQL versions < 8.0.13
     */
    private function addIndexSafely(string $table, string $indexName, array $columns): void
    {
        try {
            $columnList = implode(', ', $columns);
            DB::statement("ALTER TABLE {$table} ADD INDEX {$indexName} ({$columnList})");
        } catch (\Exception $e) {
            // Index might already exist, log but continue
            Log::debug("Could not create index {$indexName} on {$table}: " . $e->getMessage());
        }
    }

    /**
     * Safely drop an index from a table
     */
    private function dropIndexSafely(string $table, string $indexName): void
    {
        try {
            DB::statement("ALTER TABLE {$table} DROP INDEX {$indexName}");
        } catch (\Exception $e) {
            // Index might not exist, log but continue
            Log::debug("Could not drop index {$indexName} from {$table}: " . $e->getMessage());
        }
    }
};
