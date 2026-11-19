<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bimtek_events') && !Schema::hasColumn('bimtek_events', 'submission_drive_link')) {
            Schema::table('bimtek_events', function (Blueprint $table) {
                $table->text('submission_drive_link')->nullable()->after('zoom_attendance_end_at');
            });
        }

        if (Schema::hasTable('bimtek_event_sessions') && !Schema::hasColumn('bimtek_event_sessions', 'proof_drive_link')) {
            Schema::table('bimtek_event_sessions', function (Blueprint $table) {
                $table->text('proof_drive_link')->nullable()->after('attendance_end_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bimtek_event_sessions') && Schema::hasColumn('bimtek_event_sessions', 'proof_drive_link')) {
            Schema::table('bimtek_event_sessions', function (Blueprint $table) {
                $table->dropColumn('proof_drive_link');
            });
        }

        if (Schema::hasTable('bimtek_events') && Schema::hasColumn('bimtek_events', 'submission_drive_link')) {
            Schema::table('bimtek_events', function (Blueprint $table) {
                $table->dropColumn('submission_drive_link');
            });
        }
    }
};
