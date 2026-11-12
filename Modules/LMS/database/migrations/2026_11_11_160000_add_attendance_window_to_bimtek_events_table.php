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
            $table->dateTime('zoom_attendance_start_at')->nullable()->after('zoom_scheduled_at');
            $table->dateTime('zoom_attendance_end_at')->nullable()->after('zoom_attendance_start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bimtek_events', function (Blueprint $table) {
            $table->dropColumn([
                'zoom_attendance_start_at',
                'zoom_attendance_end_at',
            ]);
        });
    }
};
