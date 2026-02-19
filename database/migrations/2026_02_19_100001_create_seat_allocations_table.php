<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seat_allocations', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('registration_id')->constrained('registrations')->onDelete('cascade');
            $table->foreignId('participant_id')->nullable()->constrained('participants')->onDelete('cascade');
            $table->foreignId('destination_id')->constrained('destinations')->onDelete('restrict');
            $table->foreignId('scan_log_id')->constrained('scan_logs')->onDelete('restrict');

            // Seat Details
            $table->unsignedInteger('bus_number')->nullable();
            $table->unsignedInteger('seat_number')->nullable();
            $table->string('seat_code', 20);

            // Audit
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->constrained('admins')->onDelete('restrict');

            $table->timestamps();

            // Indexes
            $table->unique(['destination_id', 'bus_number', 'seat_number'], 'idx_seat_unique');
            $table->index('registration_id', 'idx_registration_id');
            $table->index('participant_id', 'idx_participant_id');
            $table->index(['destination_id', 'bus_number'], 'idx_destination_bus');
            $table->index('scan_log_id', 'idx_scan_log_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_allocations');
    }
};
