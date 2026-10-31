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
        Schema::table('bimtek_events', function (Blueprint $table) {
            // Fase 1: Pendaftaran
            $table->dateTime('registration_start_at')->nullable()->after('quota');
            $table->dateTime('registration_end_at')->nullable()->after('registration_start_at');

            // Jadwal Spesifik Sesi Live Zoom (Fase 2)
            $table->dateTime('zoom_scheduled_at')->nullable()->after('zoom_passcode');

            // Fase 3: Evaluasi Akhir & Pengajuan Sertifikat PPSDM
            $table->dateTime('assessment_start_at')->nullable()->after('zoom_scheduled_at');
            $table->dateTime('assessment_end_at')->nullable()->after('assessment_start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bimtek_events', function (Blueprint $table) {
            $table->dropColumn([
                'registration_start_at',
                'registration_end_at',
                'zoom_scheduled_at',
                'assessment_start_at',
                'assessment_end_at',
            ]);
        });
    }
};
