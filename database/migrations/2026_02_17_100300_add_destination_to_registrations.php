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
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('destination_id')
                ->after('form_link_id')
                ->constrained('destinations')
                ->onDelete('restrict');

            $table->index('destination_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['destination_id']);
            $table->dropIndex(['destination_id']);
            $table->dropColumn('destination_id');
        });
    }
};
