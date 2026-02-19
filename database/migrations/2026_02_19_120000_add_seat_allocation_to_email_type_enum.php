<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - add seat_allocation to email_type enum
     */
    public function up(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            // Drop the old enum and create new one with added value
            $table->enum('email_type', ['form_link', 'qr_code', 'rejection', 'seat_allocation'])->change();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('email_type', ['form_link', 'qr_code', 'rejection'])->change();
        });
    }
};
