<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Instrumen Pertanyaan Kuesioner Evaluasi Bimtek
        if (!Schema::hasTable('bimtek_evaluation_questions')) {
            Schema::create('bimtek_evaluation_questions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->text('question'); // Teks pertanyaan kuesioner
                $table->text('description')->nullable(); // Petunjuk tambahan jika ada
                $table->enum('type', ['scale', 'text'])->default('scale'); // scale (skala 1-5) atau text (uraian)
                $table->string('min_label')->nullable()->default('Sangat Kurang'); // Label nilai terendah
                $table->string('max_label')->nullable()->default('Sangat Baik'); // Label nilai tertinggi
                $table->unsignedTinyInteger('scale_max')->default(5);
                $table->string('section')->default('Narasumber'); // Pengelompokan: 'Materi & Pemateri', 'Fasilitas & Penyelenggaraan', 'Catatan & Masukan'
                $table->unsignedInteger('order')->default(1);
                $table->boolean('is_required')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'order']);
            });

            // Seed default master questions
            $defaultQuestions = [
                [
                    'id'          => (string) Str::uuid(),
                    'question'    => 'Penguasaan Materi & Kejelasan Paparan Narasumber',
                    'description' => 'Penilaian terhadap kedalaman wawasan narasumber serta sistematika penyampaian materi.',
                    'type'        => 'scale',
                    'min_label'   => 'Sangat Kurang',
                    'max_label'   => 'Sangat Baik',
                    'scale_max'   => 5,
                    'section'     => 'Materi & Pemateri',
                    'order'       => 1,
                    'is_required' => true,
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'id'          => (string) Str::uuid(),
                    'question'    => 'Metode Penyampaian, Interaktivitas, & Diskusi Sesi',
                    'description' => 'Penilaian terhadap kemampuan narasumber dalam membangun diskusi interaktif dan merespons pertanyaan peserta.',
                    'type'        => 'scale',
                    'min_label'   => 'Monoton',
                    'max_label'   => 'Sangat Interaktif',
                    'scale_max'   => 5,
                    'section'     => 'Materi & Pemateri',
                    'order'       => 2,
                    'is_required' => true,
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'id'          => (string) Str::uuid(),
                    'question'    => 'Ketepatan Waktu & Fasilitasi Jadwal Bimtek',
                    'description' => 'Penilaian terhadap kesesuaian alokasi waktu dan efisiensi manajemen agenda pelatihan.',
                    'type'        => 'scale',
                    'min_label'   => 'Sering Molor',
                    'max_label'   => 'Sangat Tepat Waktu',
                    'scale_max'   => 5,
                    'section'     => 'Fasilitas & Penyelenggaraan',
                    'order'       => 3,
                    'is_required' => true,
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'id'          => (string) Str::uuid(),
                    'question'    => 'Kualitas Sarana Pendukung & Layanan Fasilitator Bimtek',
                    'description' => 'Penilaian terhadap kelancaran platform daring (Zoom), ketersediaan materi belajar, dan keaktifan panitia.',
                    'type'        => 'scale',
                    'min_label'   => 'Kurang Memuaskan',
                    'max_label'   => 'Sangat Memuaskan',
                    'scale_max'   => 5,
                    'section'     => 'Fasilitas & Penyelenggaraan',
                    'order'       => 4,
                    'is_required' => true,
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'id'          => (string) Str::uuid(),
                    'question'    => 'Catatan, Masukan, dan Saran untuk Narasumber & Panitia Penyelenggara',
                    'description' => 'Tuliskan masukan konstruktif atau hal positif yang perlu dipertahankan untuk perbaikan mutu pelaksanaan ke depan.',
                    'type'        => 'text',
                    'min_label'   => null,
                    'max_label'   => null,
                    'scale_max'   => 5,
                    'section'     => 'Catatan & Masukan',
                    'order'       => 5,
                    'is_required' => false,
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ];

            DB::table('bimtek_evaluation_questions')->insert($defaultQuestions);
        }

        // 2. Tambah overall_score ke tabel bimtek_instructor_evaluations jika belum ada
        if (Schema::hasTable('bimtek_instructor_evaluations') && !Schema::hasColumn('bimtek_instructor_evaluations', 'overall_score')) {
            Schema::table('bimtek_instructor_evaluations', function (Blueprint $table) {
                $table->decimal('overall_score', 3, 1)->nullable()->after('feedback');
            });
        }

        // 3. Tabel Detail Jawaban Kuesioner (Bimtek Evaluation Answers)
        if (!Schema::hasTable('bimtek_evaluation_answers')) {
            Schema::create('bimtek_evaluation_answers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('evaluation_id')->constrained('bimtek_instructor_evaluations')->cascadeOnDelete();
                $table->foreignUuid('question_id')->constrained('bimtek_evaluation_questions')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating')->nullable(); // Untuk tipe 'scale' (1-5)
                $table->text('text_answer')->nullable(); // Untuk tipe 'text'
                $table->timestamps();

                $table->unique(['evaluation_id', 'question_id'], 'eval_question_unique');
                $table->index(['question_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bimtek_evaluation_answers');
        if (Schema::hasTable('bimtek_instructor_evaluations') && Schema::hasColumn('bimtek_instructor_evaluations', 'overall_score')) {
            Schema::table('bimtek_instructor_evaluations', function (Blueprint $table) {
                $table->dropColumn('overall_score');
            });
        }
        Schema::dropIfExists('bimtek_evaluation_questions');
    }
};
