<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bimtek_events') && !Schema::hasColumn('bimtek_events', 'zoom_attendance_code')) {
            Schema::table('bimtek_events', function (Blueprint $table) {
                $table->string('zoom_attendance_code', 50)->nullable()->after('zoom_passcode');
            });
        }

        if (Schema::hasTable('course_student') && !Schema::hasColumn('course_student', 'zoom_joined_at')) {
            Schema::table('course_student', function (Blueprint $table) {
                $table->timestamp('zoom_joined_at')->nullable()->after('zoom_attended');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bimtek_events') && Schema::hasColumn('bimtek_events', 'zoom_attendance_code')) {
            Schema::table('bimtek_events', function (Blueprint $table) {
                $table->dropColumn('zoom_attendance_code');
            });
        }

        if (Schema::hasTable('course_student') && Schema::hasColumn('course_student', 'zoom_joined_at')) {
            Schema::table('course_student', function (Blueprint $table) {
                $table->dropColumn('zoom_joined_at');
            });
        }
    }
};
