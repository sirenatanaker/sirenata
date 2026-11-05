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
        if (!Schema::hasTable('section_zoom_proofs')) {
            Schema::create('section_zoom_proofs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('course_section_id')->constrained('course_sections')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('file_path')->nullable();
                $table->text('drive_link')->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', ['submitted', 'verified', 'rejected'])->default('submitted');
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->unique(['course_section_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_zoom_proofs');
    }
};
