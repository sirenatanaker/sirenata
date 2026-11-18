<?php

namespace Modules\LMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestChoice;
use Modules\LMS\Models\PostTestQuestion;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PostTestSeeder extends Seeder
{
    public function run(): void
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('assignments/soal');
        $disk->makeDirectory('assignments/kunci');
        $disk->makeDirectory('assignments/demo');

        // Buat berkas Excel template soal, kunci jawaban, dan 3 dummy jawaban peserta (100%, 80%, 50%)
        $this->generateSpreadsheetDummyFiles($disk);

        $templatePath = 'assignments/soal/Template_Kertas_Kerja_Bimtek.xlsx';
        $keyPath = 'assignments/kunci/Kunci_Jawaban_Kertas_Kerja_Bimtek.xlsx';
        $templateSize = $this->formatBytes((int) $disk->size($templatePath));
        $keySize = $this->formatBytes((int) $disk->size($keyPath));

        $courses = Course::with('sections')->get()->values();

        foreach ($courses as $courseIndex => $course) {
            // 1. Evaluasi per Bab / Agenda / Section (Divariasikan: Uraian atau Upload File Excel Auto-Graded)
            foreach ($course->sections as $index => $section) {
                $isEssay = ($index % 2 === 0); // Genap (Hari 1, 3) = Uraian, Ganjil (Hari 2) = Upload Excel
                $type = $isEssay ? 'essay' : 'file_upload';

                $title = $isEssay
                    ? 'Evaluasi Uraian: ' . $section->name
                    : 'Penugasan Berkas: ' . $section->name;

                $description = $isEssay
                    ? 'Evaluasi pemahaman konsep dan analisis studi kasus melalui lembar jawaban uraian (dengan koreksi otomatis poin kunci).'
                    : 'Penugasan lembar kerja Excel studi kasus dengan pencocokan otomatis terhadap kunci jawaban Admin.';

                $cleanSecName = preg_replace('/[^A-Za-z0-9_]/', '_', $section->name);

                $test = PostTest::updateOrCreate(
                    [
                        'course_section_id' => $section->id,
                    ],
                    [
                        'course_id'            => $course->id,
                        'title'                => $title,
                        'description'          => $description,
                        'passing_score'        => $isEssay ? 70 : 75,
                        'duration'             => $isEssay ? 30 : 60,
                        'type'                 => $type,
                        'admin_file_path'      => $isEssay ? null : $templatePath,
                        'admin_file_name'      => $isEssay ? null : 'Template_Tugas_Hari_' . ($index + 1) . '_' . substr($cleanSecName, 0, 25) . '.xlsx',
                        'admin_file_size'      => $isEssay ? null : $templateSize,
                        'answer_key_file_path' => $isEssay ? null : $keyPath,
                        'answer_key_file_name' => $isEssay ? null : 'Kunci_Jawaban_Hari_' . ($index + 1) . '_' . substr($cleanSecName, 0, 25) . '.xlsx',
                        'answer_key_file_size' => $isEssay ? null : $keySize,
                        'instructions'         => $isEssay ? null : 'Unduh template Excel kertas kerja dari penyelenggara, lengkapi sel jawaban pada kolom D dan E (baris 2–6), lalu unggah kembali berkas .xlsx Anda untuk dikoreksi otomatis oleh sistem.',
                        'max_words'            => $isEssay ? 500 : null,
                    ]
                );

                if ($isEssay) {
                    $this->createEssayQuestions($test, $section->name);
                } else {
                    $test->questions()->delete();
                }
            }

            // 2. Evaluasi Akhir (course_section_id bernilai NULL - Variasi Uraian atau Upload Berkas)
            $isFinalEssay = ($courseIndex % 2 === 0);
            $finalType = $isFinalEssay ? 'essay' : 'file_upload';
            $cleanCourseName = preg_replace('/[^A-Za-z0-9_]/', '_', $course->name);

            $finalTest = PostTest::updateOrCreate(
                [
                    'course_id'         => $course->id,
                    'course_section_id' => null,
                ],
                [
                    'title'                => 'Evaluasi Akhir: ' . $course->name,
                    'description'          => $isFinalEssay
                        ? 'Evaluasi komprehensif akhir pelatihan berbasis uraian dengan koreksi otomatis poin kunci.'
                        : 'Penugasan portofolio lembar kerja Excel akhir pelatihan dengan koreksi otomatis.',
                    'passing_score'        => 75,
                    'duration'             => $isFinalEssay ? 45 : 60,
                    'type'                 => $finalType,
                    'admin_file_path'      => $isFinalEssay ? null : $templatePath,
                    'admin_file_name'      => $isFinalEssay ? null : 'Template_Evaluasi_Akhir_' . substr($cleanCourseName, 0, 25) . '.xlsx',
                    'admin_file_size'      => $isFinalEssay ? null : $templateSize,
                    'answer_key_file_path' => $isFinalEssay ? null : $keyPath,
                    'answer_key_file_name' => $isFinalEssay ? null : 'Kunci_Evaluasi_Akhir_' . substr($cleanCourseName, 0, 25) . '.xlsx',
                    'answer_key_file_size' => $isFinalEssay ? null : $keySize,
                    'instructions'         => $isFinalEssay ? null : 'Unduh template Excel evaluasi akhir di atas, lengkapi seluruh sel isian pada kolom D dan E (baris 2–6), lalu unggah kembali berkas .xlsx Anda untuk penilaian otomatis.',
                    'max_words'            => $isFinalEssay ? 600 : null,
                ]
            );

            if ($isFinalEssay) {
                $this->createEssayQuestions($finalTest, $course->name);
            } else {
                $finalTest->questions()->delete();
            }
        }

        $this->command->info("PostTestSeeder (Koreksi Otomatis Upload Excel & Uraian beserta Dummy) berhasil dijalankan 🚀");
    }

    private function createEssayQuestions(PostTest $test, string $contextName): void
    {
        $test->questions()->delete();

        $questions = [
            [
                'question'   => 'Jelaskan langkah-langkah strategis dan metodologi analisis yang tepat dalam implementasi materi ' . $contextName . ' di instansi Anda!',
                'answer_key' => 'pemetaan regulasi, pengumpulan data primer, analisis kebutuhan, validasi indikator, ' . mb_strtolower($contextName),
            ],
            [
                'question'   => 'Berdasarkan studi kasus pada modul ' . $contextName . ', berikan analisis komparatif serta rekomendasi mitigasi risiko terhadap kendala yang dihadapi di lapangan!',
                'answer_key' => 'sinkronisasi data, koordinasi lintas unit, standardisasi SOP, audit kepatuhan, mitigasi risiko',
            ],
        ];

        foreach ($questions as $q) {
            PostTestQuestion::create([
                'post_test_id' => $test->id,
                'question'     => $q['question'],
                'answer_key'   => $q['answer_key'],
            ]);
        }
    }

    private function generateSpreadsheetDummyFiles($disk): void
    {
        $baseRows = [
            ['No', 'Aspek Penilaian Ketenagakerjaan', 'Indikator Kinerja', 'Nilai Target / Kuantitatif', 'Status Kepatuhan / Strategi'],
            [1, 'Pemetaan Kebutuhan Tenaga Kerja', 'Cakupan sektor prioritas (%)', '', ''],
            [2, 'Rasio Elastisitas Kesempatan Kerja', 'Koefisien proyeksi tahunan', '', ''],
            [3, 'Validasi Data Primer Daerah', 'Kelengkapan pelaporan unit', '', ''],
            [4, 'Standar Operasional Prosedur', 'Ketersediaan SOP terintegrasi', '', ''],
            [5, 'Koordinasi Lintas Instansi', 'Frekuensi sinkronisasi / tahun', '', ''],
        ];

        $answerKeyRows = [
            ['No', 'Aspek Penilaian Ketenagakerjaan', 'Indikator Kinerja', 'Nilai Target / Kuantitatif', 'Status Kepatuhan / Strategi'],
            [1, 'Pemetaan Kebutuhan Tenaga Kerja', 'Cakupan sektor prioritas (%)', 85, 'Sesuai Regulasi'],
            [2, 'Rasio Elastisitas Kesempatan Kerja', 'Koefisien proyeksi tahunan', 1.25, 'Proyeksi Moderat'],
            [3, 'Validasi Data Primer Daerah', 'Kelengkapan pelaporan unit', 100, 'Tervalidasi'],
            [4, 'Standar Operasional Prosedur', 'Ketersediaan SOP terintegrasi', 12, 'Tersedia Lengkap'],
            [5, 'Koordinasi Lintas Instansi', 'Frekuensi sinkronisasi / tahun', 4, 'Berjalan Rutin'],
        ];

        // 100% cocok (10 dari 10 sel D2:E6)
        $perfectRows = $answerKeyRows;

        // 80% cocok (8 dari 10 sel cocok; D5 dan E6 berbeda)
        $partial80Rows = $answerKeyRows;
        $partial80Rows[4][3] = 6;              // D5 salah (seharusnya 12)
        $partial80Rows[5][4] = 'Belum Rutin';  // E6 salah (seharusnya Berjalan Rutin)

        // 50% cocok (5 dari 10 sel cocok; 5 sel salah)
        $fail50Rows = $answerKeyRows;
        $fail50Rows[1][3] = 50;
        $fail50Rows[2][4] = 'Tidak Sesuai';
        $fail50Rows[3][3] = 45;
        $fail50Rows[4][4] = 'Dalam Proses';
        $fail50Rows[5][3] = 1;

        $this->writeXlsx($disk->path('assignments/soal/Template_Kertas_Kerja_Bimtek.xlsx'), $baseRows);
        $this->writeXlsx($disk->path('assignments/kunci/Kunci_Jawaban_Kertas_Kerja_Bimtek.xlsx'), $answerKeyRows);
        $this->writeXlsx($disk->path('assignments/demo/Jawaban_Peserta_Sempurna_100.xlsx'), $perfectRows);
        $this->writeXlsx($disk->path('assignments/demo/Jawaban_Peserta_Sebagian_80.xlsx'), $partial80Rows);
        $this->writeXlsx($disk->path('assignments/demo/Jawaban_Peserta_Perlu_Revisi_50.xlsx'), $fail50Rows);
    }

    private function writeXlsx(string $fullPath, array $rows): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Lembar Kerja');
        $sheet->fromArray($rows, null, 'A1');

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);
    }

    private function formatBytes(int $bytes): string
    {
        return $bytes >= 1048576
            ? round($bytes / 1048576, 1) . ' MB'
            : max(1, (int) round($bytes / 1024)) . ' KB';
    }

    private function createMultipleChoiceQuestions(PostTest $test, string $contextName): void
    {
        if ($test->questions()->count() > 0) {
            return;
        }

        $sampleQuestions = [
            [
                'q'       => 'Apa tujuan fundamental dari pengolahan data pada konteks ' . $contextName . '?',
                'correct' => 'Memastikan proyeksi dan alokasi tenaga kerja akurat sesuai regulasi',
                'wrongs'  => [
                    'Mengurangi transparansi pelaporan administrasi daerah',
                    'Menghapus kewajiban standarisasi data di tingkat pusat',
                    'Memperpanjang birokrasi perizinan instansi vertikal',
                ],
            ],
            [
                'q'       => 'Manakah langkah awal yang paling krusial dalam tahap implementasi ' . $contextName . '?',
                'correct' => 'Melakukan inventarisasi dan validasi variabel input primer',
                'wrongs'  => [
                    'Langsung menetapkan target kelulusan tanpa telaah data',
                    'Mengabaikan data demografi ketenagakerjaan wilayah',
                    'Menerbitkan sertifikat sebelum pengujian materi',
                ],
            ],
            [
                'q'       => 'Bagaimana mengukur keberhasilan indikator pada ' . $contextName . '?',
                'correct' => 'Tercapainya standar kepatuhan KKM dan keselarasan proyeksi',
                'wrongs'  => [
                    'Tingginya jumlah pengulangan tes oleh peserta',
                    'Penurunan efisiensi jam pelajaran tenaga pendidik',
                    'Tidak tersedianya dokumen portofolio hasil evaluasi',
                ],
            ],
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
