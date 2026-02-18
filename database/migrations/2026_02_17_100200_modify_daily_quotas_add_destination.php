<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_quotas', function (Blueprint $table) {
            // Drop old unique constraint on date only
            $table->dropUnique(['date']);

            // Add destination_id
            $table->foreignId('destination_id')
                ->after('id')
                ->constrained('destinations')
                ->onDelete('cascade');

            // Rename columns for clarity
            $table->renameColumn('quota', 'quota_daily');
            $table->renameColumn('used', 'used_daily');
            $table->renameColumn('remaining', 'remaining_daily');

            // Add new unique constraint on destination_id + date
            $table->unique(['destination_id', 'date']);
            $table->index('destination_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_quotas', function (Blueprint $table) {
            // Drop new constraints
            $table->dropUnique(['destination_id', 'date']);
            $table->dropIndex(['destination_id']);
            $table->dropForeign(['destination_id']);
            $table->dropColumn('destination_id');

            // Rename back
            $table->renameColumn('quota_daily', 'quota');
            $table->renameColumn('used_daily', 'used');
            $table->renameColumn('remaining_daily', 'remaining');

            // Restore old unique constraint
            $table->unique('date');
        });
    }
};
