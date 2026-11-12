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
        if (!Schema::hasTable('bimtek_event_sessions')) {
            Schema::create('bimtek_event_sessions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('bimtek_event_id')->constrained('bimtek_events')->cascadeOnDelete();
                $table->unsignedInteger('session_number')->default(1);
                $table->string('title'); // e.g. "Sesi Hari 1: Pembukaan & Materi Kebijakan"
                $table->date('session_date'); // e.g. 2026-11-20
                $table->dateTime('start_time'); // e.g. 2026-11-20 09:00:00
                $table->dateTime('end_time')->nullable(); // e.g. 2026-11-20 12:00:00
                $table->text('zoom_url')->nullable();
                $table->string('zoom_meeting_id')->nullable();
                $table->string('zoom_passcode')->nullable();
                $table->string('attendance_code')->nullable();
                $table->dateTime('attendance_start_at')->nullable();
                $table->dateTime('attendance_end_at')->nullable();
                $table->timestamps();

                $table->index(['bimtek_event_id', 'session_date']);
            });
        }

        if (!Schema::hasTable('bimtek_session_attendances')) {
            Schema::create('bimtek_session_attendances', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('bimtek_event_session_id')->constrained('bimtek_event_sessions')->cascadeOnDelete();
                $table->foreignUuid('bimtek_event_id')->constrained('bimtek_events')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
                $table->timestamp('attended_at')->useCurrent();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->unique(['bimtek_event_session_id', 'user_id'], 'session_user_unique');
                $table->index(['bimtek_event_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bimtek_session_attendances');
        Schema::dropIfExists('bimtek_event_sessions');
    }
};
