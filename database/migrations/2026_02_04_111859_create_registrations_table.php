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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_link_id')->unique()->constrained()->onDelete('cascade');
            
            // Data Perwakilan
            $table->string('representative_name');
            $table->string('representative_nik', 16)->unique(); 
            $table->date('representative_birth_date');
            
            // Data Keluarga
            $table->unsignedInteger('family_count');
            $table->string('kk_number', 16)->unique();
            $table->string('kk_document_path', 500);
            $table->boolean('has_child_under_4')->default(false);
            
            // Admin Notes
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};