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
        if (!Schema::hasTable('bimtek_instructor_evaluations')) {
            Schema::create('bimtek_instructor_evaluations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('bimtek_event_id')->constrained('bimtek_events')->cascadeOnDelete();
                $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating_materi')->default(5);
                $table->unsignedTinyInteger('rating_penyampaian')->default(5);
                $table->unsignedTinyInteger('rating_fasilitasi')->default(5);
                $table->text('feedback')->nullable();
                $table->timestamps();

                $table->unique(['bimtek_event_id', 'user_id'], 'bimtek_eval_event_user_unique');
                $table->index(['bimtek_event_id']);
                $table->index(['course_id']);
                $table->index(['user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bimtek_instructor_evaluations');
    }
};
