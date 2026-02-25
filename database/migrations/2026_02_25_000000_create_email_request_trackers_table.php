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
        Schema::create('email_request_trackers', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // 2026-02-25
            $table->tinyInteger('hour'); // 0-23 (hour of the day)
            $table->unsignedInteger('request_count')->default(0);
            $table->timestamp('window_start')->useCurrent(); // 2026-02-25 13:00:00
            $table->timestamp('window_end')->useCurrent(); // 2026-02-25 14:00:00
            $table->timestamps();

            // Unique constraint for date + hour combination
            $table->unique(['date', 'hour']);

            // Index for quick lookup
            $table->index(['date', 'hour', 'request_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_request_trackers');
    }
};
