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
        Schema::create('registration_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('destination_id');
            $table->string('filename');
            $table->integer('total_registrations');
            $table->integer('total_participants');
            $table->integer('successful');
            $table->integer('failed')->default(0);
            $table->json('error_details')->nullable();
            $table->enum('status', ['completed', 'failed', 'partial'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins');
            $table->foreign('destination_id')->references('id')->on('destinations');
            $table->index(['created_at', 'destination_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_imports');
    }
};
