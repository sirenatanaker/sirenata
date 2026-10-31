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
        Schema::create('certificate_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bimtek_event_id')->constrained('bimtek_events')->cascadeOnDelete();
            $table->string('submission_code')->unique(); // misal: "SUB-PPSDM-2026-001"
            $table->string('submission_letter_number')->nullable(); // Nomor Nota Dinas Pengajuan
            $table->string('ppsdm_decree_number')->nullable(); // Nomor SK/Surat Balasan PPSDM
            $table->integer('total_graduates')->default(0);
            
            // Relasi ke User pembuat pengajuan
            $table->foreignUuid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('numbered_at')->nullable();

            // Status Pengajuan
            $table->enum('status', ['draft', 'submitted_to_ppsdm', 'numbered', 'completed'])->default('draft');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_submissions');
    }
};
