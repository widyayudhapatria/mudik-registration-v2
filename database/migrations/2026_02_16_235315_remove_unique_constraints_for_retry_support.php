<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove unique constraints to support retry after rejection.
     * Application-level validation will handle uniqueness for active records only.
     */
    public function up(): void
    {
        // Remove unique constraint from form_links.email
        // Allow same email to create multiple form_links (for retry)
        Schema::table('form_links', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        // Remove unique constraints from registrations
        // Keep form_link_id unique (1:1 relationship)
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropUnique(['representative_nik']);
            $table->dropUnique(['kk_number']);
        });

        // Remove unique constraint from participants.nik_kia
        Schema::table('participants', function (Blueprint $table) {
            $table->dropUnique(['nik_kia']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Re-add unique constraints (for rollback).
     */
    public function down(): void
    {
        // Re-add unique constraint to form_links.email
        Schema::table('form_links', function (Blueprint $table) {
            $table->unique('email');
        });

        // Re-add unique constraints to registrations
        Schema::table('registrations', function (Blueprint $table) {
            $table->unique('representative_nik');
            $table->unique('kk_number');
        });

        // Re-add unique constraint to participants.nik_kia
        Schema::table('participants', function (Blueprint $table) {
            $table->unique('nik_kia');
        });
    }
};
