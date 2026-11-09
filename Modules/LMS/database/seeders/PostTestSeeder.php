<?php

namespace Modules\LMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestQuestion;
use Modules\LMS\Models\PostTestChoice;

class PostTestSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan dummy berkas soal admin tersedia di storage public
        Storage::disk('public')->makeDirectory('assignments/soal');
        if (!Storage::disk('public')->exists('assignments/soal/Soal_Tugas_Studi_Kasus.pdf')) {
            Storage::disk('public')->put(
                'assignments/soal/Soal_Tugas_Studi_Kasus.pdf',
                "%PDF-1.4\n1 0 obj\n<< /Title (Lembar Soal Penugasan Studi Kasus) /Author (Admin Penyelenggara) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF"
            );
        }

        $courses = Course::with('sections')->get();

        foreach ($courses as $course) {
            // 1. Evaluasi per Bab / Agenda / Section (Divariasikan: Uraian atau Upload File saja)
            foreach ($course->sections as $index => $section) {
                $isEssay = ($index % 2 === 0); // Genap (Hari 1, 3) = Uraian, Ganjil (Hari 2) = Upload File
                $type = $isEssay ? 'essay' : 'file_upload';

                $title = $isEssay
                    ? 'Evaluasi Uraian: ' . $section->name
                    : 'Penugasan Berkas: ' . $section->name;

                $description = $isEssay
                    ? 'Evaluasi pemahaman konsep dan analisis studi kasus melalui lembar jawaban uraian.'
                    : 'Penugasan berkas mandiri studi kasus dan penyusunan instrumen lembar kerja.';

                $cleanSecName = preg_replace('/[^A-Za-z0-9_]/', '_', $section->name);

                $test = PostTest::updateOrCreate(
                    [
                        'course_section_id' => $section->id,
                    ],
                    [
                        'course_id'        => $course->id,
                        'title'            => $title,
                        'description'      => $description,
                        'passing_score'    => $isEssay ? 70 : 75,
                        'duration'         => $isEssay ? 30 : 60,
                        'type'             => $type,
                        'admin_file_path'  => $isEssay ? null : 'assignments/soal/Soal_Tugas_Studi_Kasus.pdf',
                        'admin_file_name'  => $isEssay ? null : 'Soal_Tugas_Hari_' . ($index + 1) . '_' . substr($cleanSecName, 0, 30) . '.pdf',
                        'admin_file_size'  => $isEssay ? null : '2.4 MB',
                        'instructions'     => $isEssay ? null : 'Unduh berkas lembar kerja soal dari penyelenggara di atas, kerjakan dokumen tugas sesuai panduan, lalu unggah kembali berkas hasil pengerjaan Anda dalam format PDF atau DOCX.',
                        'max_words'        => $isEssay ? 500 : null,
                    ]
                );

                // Buat pertanyaan untuk tipe uraian
                if ($isEssay) {
                    $this->createEssayQuestions($test, $section->name);
                }
            }

            // 2. Evaluasi Akhir (course_section_id bernilai NULL - Multiple Choice CBT)
            $finalTest = PostTest::updateOrCreate(
                [
                    'course_id'         => $course->id,
                    'course_section_id' => null,
                ],
                [
                    'title'         => 'Evaluasi Akhir: ' . $course->name,
                    'description'   => 'Ujian kelulusan akhir komprehensif seluruh materi kursus.',
                    'passing_score' => 75,
                    'duration'      => 30,
                    'type'          => 'multiple_choice',
                ]
            );

            $this->createMultipleChoiceQuestions($finalTest, $course->name);
        }

        $this->command->info("PostTestSeeder (Variasi Uraian & Upload File pada Agenda Harian) berhasil dijalankan 🚀");
    }

    private function createEssayQuestions(PostTest $test, string $contextName): void
    {
        // Bersihkan soal lama jika ada agar tidak duplikasi
        $test->questions()->delete();

        $questions = [
            'Jelaskan langkah-langkah strategis dan metodologi analisis yang tepat dalam implementasi materi ' . $contextName . ' di instansi Anda!',
            'Berdasarkan studi kasus pada modul ' . $contextName . ', berikan analisis komparatif serta rekomendasi mitigasi risiko terhadap kendala yang dihadapi di lapangan!',
        ];

        foreach ($questions as $qText) {
            PostTestQuestion::create([
                'post_test_id' => $test->id,
                'question'     => $qText,
            ]);
        }
    }

    private function createMultipleChoiceQuestions(PostTest $test, string $contextName): void
    {
        if ($test->questions()->count() > 0) {
            return;
        }

        $sampleQuestions = [
            [
                'q' => 'Apa tujuan fundamental dari pengolahan data pada konteks ' . $contextName . '?',
                'correct' => 'Memastikan proyeksi dan alokasi tenaga kerja akurat sesuai regulasi',
                'wrongs'  => [
                    'Mengurangi transparansi pelaporan administrasi daerah',
                    'Menghapus kewajiban standarisasi data di tingkat pusat',
                    'Memperpanjang birokrasi perizinan instansi vertikal'
                ]
            ],
            [
                'q' => 'Manakah langkah awal yang paling krusial dalam tahap implementasi ' . $contextName . '?',
                'correct' => 'Melakukan inventarisasi dan validasi variabel input primer',
                'wrongs'  => [
                    'Langsung menetapkan target kelulusan tanpa telaah data',
                    'Mengabaikan data demografi ketenagakerjaan wilayah',
                    'Menerbitkan sertifikat sebelum pengujian materi'
                ]
            ],
            [
                'q' => 'Bagaimana mengukur keberhasilan indikator pada ' . $contextName . '?',
                'correct' => 'Tercapainya standar kepatuhan KKM dan keselarasan proyeksi',
                'wrongs'  => [
                    'Tingginya jumlah pengulangan tes oleh peserta',
                    'Penurunan efisiensi jam pelajaran tenaga pendidik',
                    'Tidak tersedianya dokumen portofolio hasil evaluasi'
                ]
            ]
        ];

        foreach ($sampleQuestions as $item) {
            $question = PostTestQuestion::create([
                'post_test_id' => $test->id,
                'question'     => $item['q'],
            ]);

            PostTestChoice::create([
                'post_test_question_id' => $question->id,
                'choice'                => $item['correct'],
                'is_correct'            => true,
            ]);

            foreach ($item['wrongs'] as $wrong) {
                PostTestChoice::create([
                    'post_test_question_id' => $question->id,
                    'choice'                => $wrong,
                    'is_correct'            => false,
                ]);
            }
        }
    }
}
