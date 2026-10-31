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
        Schema::table('course_student', function (Blueprint $table) {
            $table->foreignUuid('bimtek_event_id')->nullable()->after('course_id')->constrained('bimtek_events')->nullOnDelete();
            $table->foreignUuid('cert_submission_id')->nullable()->after('bimtek_event_id')->constrained('certificate_submissions')->nullOnDelete();
            $table->enum('cert_submission_status', ['belum_diajukan', 'diajukan_ke_ppsdm', 'terbit'])->default('belum_diajukan')->after('status');
            $table->string('official_cert_number')->nullable()->after('certificate_code'); // Nomor Resmi dari PPSDM
            $table->boolean('zoom_attended')->default(false)->after('official_cert_number');
            $table->dateTime('zoom_attended_at')->nullable()->after('zoom_attended');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->dropForeign(['bimtek_event_id']);
            $table->dropForeign(['cert_submission_id']);
            $table->dropColumn([
                'bimtek_event_id',
                'cert_submission_id',
                'cert_submission_status',
                'official_cert_number',
                'zoom_attended',
                'zoom_attended_at',
            ]);
        });
    }
};
