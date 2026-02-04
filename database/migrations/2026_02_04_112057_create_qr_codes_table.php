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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->unique()->constrained()->onDelete('cascade');
            $table->string('token_qr', 64)->unique();
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->timestamp('scanned_at')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();

            $table->unique('registration_id');
            $table->unique('token_qr');
            $table->index('scanned_at');
            $table->index(['valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};