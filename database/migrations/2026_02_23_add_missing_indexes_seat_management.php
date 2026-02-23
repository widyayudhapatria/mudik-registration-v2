<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing indexes untuk seat_allocations dan seat_counters
     * Fokus pada:
     * 1. Unique constraint (registration_id, participant_id) - CRITICAL untuk prevent duplicate allocation
     * 2. Indexes untuk query performance
     */
    public function up(): void
    {
        // ✅ 1. Handle seat_allocations table
        if (Schema::hasTable('seat_allocations')) {
            $this->upgradeSeatsAllocationsIndexes();
        } else {
            Log::warning('seat_allocations table does not exist, skipping');
        }

        // ✅ 2. Handle seat_counters table
        if (Schema::hasTable('seat_counters')) {
            $this->upgradeSeatCountersIndexes();
        } else {
            Log::warning('seat_counters table does not exist, skipping');
        }
    }

    public function down(): void
    {
        // Rollback untuk seat_allocations
        if (Schema::hasTable('seat_allocations')) {
            $this->dropSeatsAllocationIndexes();
        }

        // Rollback untuk seat_counters
        if (Schema::hasTable('seat_counters')) {
            $this->dropSeatCountersIndexes();
        }
    }

    /**
     * Upgrade seat_allocations indexes
     */
    private function upgradeSeatsAllocationsIndexes(): void
    {
        // ✅ 1. Add UNIQUE constraint: (registration_id, participant_id) - CRITICAL
        // Prevent duplicate allocation per participant
        $this->addUniqueSafely(
            'seat_allocations',
            ['registration_id', 'participant_id'],
            'unique_allocation_per_participant'
        );

        // ✅ 2. Add simple indexes
        $this->addIndexSafely('seat_allocations', 'idx_seat_allocated_at', ['assigned_at']);
        $this->addIndexSafely('seat_allocations', 'idx_seat_assigned_by', ['assigned_by']);

        // ✅ 3. Add composite index untuk dashboard queries
        $this->addIndexSafely(
            'seat_allocations',
            'idx_seat_dest_assigned_at',
            ['destination_id', 'assigned_at']
        );

        // ✅ 4. Add composite index untuk bus seat queries
        $this->addIndexSafely(
            'seat_allocations',
            'idx_seat_bus_number_seat_number',
            ['bus_number', 'seat_number']
        );

        Log::info('seat_allocations indexes upgraded successfully');
    }

    /**
     * Upgrade seat_counters indexes
     */
    private function upgradeSeatCountersIndexes(): void
    {
        // ✅ 1. Add index pada last_allocation_at (untuk query recent allocations)
        $this->addIndexSafely('seat_counters', 'idx_count_last_allocation_at', ['last_allocation_at']);

        // ✅ 2. Add index pada created_at (untuk time-based queries)
        $this->addIndexSafely('seat_counters', 'idx_count_created_at', ['created_at']);

        Log::info('seat_counters indexes upgraded successfully');
    }

    /**
     * Safely add an index
     */
    private function addIndexSafely(string $table, string $indexName, array $columns): void
    {
        try {
            // ✅ Check if index already exists
            $exists = $this->indexExists($table, $indexName);

            if ($exists) {
                Log::debug("Index {$indexName} already exists on {$table}, skipping");
                return;
            }

            $columnList = implode(', ', $columns);
            DB::statement("ALTER TABLE {$table} ADD INDEX {$indexName} ({$columnList})");

            Log::info("Index {$indexName} created on {$table}");
        } catch (\Exception $e) {
            Log::warning("Could not create index {$indexName} on {$table}: " . $e->getMessage());
            // Don't throw - continue migration
        }
    }

    /**
     * Safely add a UNIQUE constraint
     */
    private function addUniqueSafely(string $table, array $columns, string $constraintName): void
    {
        try {
            // ✅ Check if constraint already exists
            $exists = $this->constraintExists($table, $constraintName);

            if ($exists) {
                Log::debug("Unique constraint {$constraintName} already exists on {$table}, skipping");
                return;
            }

            // ✅ Check if unique index exists with different name
            $columnList = implode(', ', $columns);
            $indexExists = DB::select("
                SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_NAME = ?
                AND COLUMN_NAME IN ('" . implode("','", $columns) . "')
                AND SEQ_IN_INDEX = 1
                AND NON_UNIQUE = 0
                AND TABLE_SCHEMA = DATABASE()
            ", [$table]);

            if (!empty($indexExists)) {
                Log::debug("Unique index on {$constraintName} columns already exists on {$table}, skipping");
                return;
            }

            DB::statement("ALTER TABLE {$table} ADD UNIQUE {$constraintName} ({$columnList})");

            Log::info("Unique constraint {$constraintName} created on {$table}");
        } catch (\Exception $e) {
            Log::warning("Could not create unique constraint {$constraintName} on {$table}: " . $e->getMessage());
            // Don't throw - continue migration
        }
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $exists = DB::select("
            SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_NAME = ?
            AND INDEX_NAME = ?
            AND TABLE_SCHEMA = DATABASE()
            LIMIT 1
        ", [$table, $indexName]);

        return !empty($exists);
    }

    /**
     * Check if constraint exists
     */
    private function constraintExists(string $table, string $constraintName): bool
    {
        $exists = DB::select("
            SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
            AND TABLE_SCHEMA = DATABASE()
            LIMIT 1
        ", [$table, $constraintName]);

        return !empty($exists);
    }

    /**
     * Drop seat_allocations indexes (for rollback)
     */
    private function dropSeatsAllocationIndexes(): void
    {
        $this->dropIndexSafely('seat_allocations', 'unique_allocation_per_participant');
        $this->dropIndexSafely('seat_allocations', 'idx_seat_allocated_at');
        $this->dropIndexSafely('seat_allocations', 'idx_seat_assigned_by');
        $this->dropIndexSafely('seat_allocations', 'idx_seat_dest_assigned_at');
        $this->dropIndexSafely('seat_allocations', 'idx_seat_bus_number_seat_number');

        Log::info('seat_allocations indexes dropped');
    }

    /**
     * Drop seat_counters indexes (for rollback)
     */
    private function dropSeatCountersIndexes(): void
    {
        $this->dropIndexSafely('seat_counters', 'idx_count_last_allocation_at');
        $this->dropIndexSafely('seat_counters', 'idx_count_created_at');

        Log::info('seat_counters indexes dropped');
    }

    /**
     * Safely drop an index
     */
    private function dropIndexSafely(string $table, string $indexName): void
    {
        try {
            $exists = $this->indexExists($table, $indexName) || $this->constraintExists($table, $indexName);

            if (!$exists) {
                Log::debug("Index/Constraint {$indexName} does not exist on {$table}, skipping drop");
                return;
            }

            DB::statement("ALTER TABLE {$table} DROP INDEX {$indexName}");

            Log::info("Index/Constraint {$indexName} dropped from {$table}");
        } catch (\Exception $e) {
            Log::warning("Could not drop index {$indexName} from {$table}: " . $e->getMessage());
            // Don't throw - continue rollback
        }
    }
};
