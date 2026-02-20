<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seat_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained('destinations')->onDelete('cascade');

            $table->unsignedInteger('current_bus_number')->default(1);
            $table->unsignedInteger('current_seat_number')->default(0); // 0-50
            $table->unsignedInteger('total_seats_allocated')->default(0);

            $table->timestamp('last_allocation_at')->nullable();

            $table->timestamps();

            $table->unique('destination_id', 'idx_destination');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_counters');
    }
};
