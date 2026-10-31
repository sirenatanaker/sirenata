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
        Schema::create('bimtek_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('batch_name'); // misal: "Bimtek Tata Kelola SPBE Angkatan I 2026"
            $table->string('batch_code')->unique(); // misal: "BIMTEK-SPBE-2026-I"
            $table->enum('execution_type', ['online', 'offline', 'hybrid'])->default('online');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('quota')->default(100);
            
            // Detail Online Zoom
            $table->text('zoom_url')->nullable();
            $table->string('zoom_meeting_id')->nullable();
            $table->string('zoom_passcode')->nullable();

            // Detail Offline (jika diperlukan)
            $table->string('location_name')->nullable();
            $table->text('location_address')->nullable();

            // Status Pelaksanaan
            $table->enum('status', ['draft', 'open_registration', 'ongoing', 'completed'])->default('draft');
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bimtek_events');
    }
};
