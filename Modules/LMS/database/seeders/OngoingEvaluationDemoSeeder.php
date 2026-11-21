<?php

namespace Modules\LMS\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestResult;

class OngoingEvaluationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $ongoingEvents = BimtekEvent::with('course')
            ->orderBy('start_date')
            ->get()
            ->filter(fn (BimtekEvent $ev) => $ev->status_badge['label'] === 'Sedang Berlangsung');

        // Pool pengguna untuk melengkapi event ongoing yang belum memiliki peserta
        $fallbackUserIds = User::query()
            ->whereHas('profile')
            ->orderBy('id')
            ->limit(25)
            ->pluck('id');

        if ($fallbackUserIds->isEmpty()) {
            $fallbackUserIds = User::query()->orderBy('id')->limit(25)->pluck('id');
        }

        $excelScenarios = [
            [
                'file_path' => 'assignments/demo/Jawaban_Peserta_Sempurna_100.xlsx',
                'file_name' => 'Lembar_Kerja_Analisis_Lengkap.xlsx',
                'notes'     => 'Seluruh kolom perhitungan (D2-D6) dan keterangan analisis (E2-E6) sudah saya lengkapi sesuai pedoman modul.',
            ],
            [
                'file_path' => 'assignments/demo/Jawaban_Peserta_Sempurna_100.xlsx',
                'file_name' => 'Kertas_Kerja_Bimtek_Final.xlsx',
                'notes'     => 'Perhitungan dan klasifikasi hasil analisis telah diverifikasi menggunakan rumus Excel sesuai template.',
            ],
            [
                'file_path' => 'assignments/demo/Jawaban_Peserta_Sebagian_80.xlsx',
                'file_name' => 'Tugas_Praktik_Analisis_Data.xlsx',
                'notes'     => 'Berikut hasil pengerjaan lembar kerja studi kasus harian, mohon koreksi bila ada indikator yang kurang tepat.',
            ],
            [
                'file_path' => 'assignments/demo/Jawaban_Peserta_Sebagian_80.xlsx',
                'file_name' => 'Kertas_Kerja_Harian_Peserta.xlsx',
                'notes'     => 'Sudah dikerjakan berdasarkan data studi kasus yang dibagikan narasumber pada sesi Zoom.',
            ],
            [
                'file_path' => 'assignments/demo/Jawaban_Peserta_Sebagian_70.xlsx',
                'file_name' => 'Jawaban_Studi_Kasus_Excel.xlsx',
                'notes'     => 'Beberapa baris indikator sudah saya hitung sesuai data daerah, mohon arahannya.',
            ],
            [
                'file_path' => 'assignments/demo/Jawaban_Peserta_Sinonim.xlsx',
                'file_name' => 'Lembar_Kerja_Analisis_Mandiri.xlsx',
                'notes'     => 'Angka perhitungan sudah disesuaikan dengan soal dan keterangan saya tulis dengan redaksi sendiri.',
            ],
            [
                'file_path' => 'assignments/demo/Jawaban_Peserta_Perlu_Revisi_50.xlsx',
                'file_name' => 'Draft_Kertas_Kerja_Analisis.xlsx',
                'notes'     => 'Mohon maaf beberapa sel masih menggunakan estimasi awal karena keterbatasan data unit.',
            ],
            [
                'file_path' => 'assignments/demo/Jawaban_Peserta_Format_Berbeda.xlsx',
                'file_name' => 'Laporan_Perhitungan_Custom.xlsx',
                'notes'     => 'Saya menambahkan kolom catatan tambahan di lembar kerja agar lebih mudah dibaca.',
            ],
            [
                'file_path' => 'assignments/demo/Jawaban_Peserta_Sempurna_100.xlsx',
                'file_name' => 'Final_Kertas_Kerja_Evaluasi.xlsx',
                'notes'     => 'Sudah final dan seluruh formula sudah dicek ulang sebelum diunggah.',
            ],
            [
                'file_path' => 'assignments/demo/Jawaban_Peserta_Kosong.xlsx',
                'file_name' => 'Template_Belum_Terisi.xlsx',
                'notes'     => 'Terlampir file lembar kerja (mohon izin jika ada sel yang belum tersimpan).',
            ],
        ];

        $totalCreated = 0;

        foreach ($ongoingEvents as $event) {
            if (!$event->course_id) {
                continue;
            }

            $enrolledIds = DB::table('course_student')
                ->where('bimtek_event_id', $event->id)
                ->pluck('user_id');

            // Jika event sedang berjalan belum memiliki peserta (mis. Bimtek Batch 1 Sumatera), daftarkan 15 peserta demo
            if ($enrolledIds->count() < 12) {
                $needed = 15 - $enrolledIds->count();
                $alreadyInCourse = DB::table('course_student')
                    ->where('course_id', $event->course_id)
                    ->pluck('user_id');

                $candidates = $fallbackUserIds->diff($alreadyInCourse)->take($needed);

                foreach ($candidates as $uid) {
                    DB::table('course_student')->insert([
                        'course_id'       => $event->course_id,
                        'user_id'         => $uid,
                        'bimtek_event_id' => $event->id,
                        'progress'        => rand(45, 80),
                        'status'          => 'in_progress',
                        'created_at'      => now()->subDays(rand(3, 6)),
                        'updated_at'      => now()->subHours(rand(2, 24)),
                    ]);
                }

                $enrolledIds = DB::table('course_student')
                    ->where('bimtek_event_id', $event->id)
                    ->pluck('user_id');
            }

            $postTests = PostTest::where('course_id', $event->course_id)
                ->with('questions')
                ->orderByRaw('course_section_id IS NULL, id')
                ->get();

            $courseName = $event->course?->name ?? $event->batch_name;

            foreach ($postTests as $pt) {
                // Pastikan evaluasi terbuka agar siap didemokan
                if ($pt->is_submission_locked) {
                    $pt->update(['is_submission_locked' => false]);
                }

                $existingUserIds = PostTestResult::where('post_test_id', $pt->id)
                    ->whereIn('user_id', $enrolledIds)
                    ->pluck('user_id');

                $pendingCountAlready = PostTestResult::where('post_test_id', $pt->id)
                    ->whereIn('user_id', $enrolledIds)
                    ->where('grading_status', PostTestResult::GRADING_PENDING)
                    ->count();

                $targetPending = $pt->type === 'file_upload' ? 10 : 8;
                $toCreate = max(0, $targetPending - $pendingCountAlready);
                if ($toCreate === 0) {
                    continue;
                }

                $availableUsers = $enrolledIds->diff($existingUserIds)->values()->take($toCreate);

                foreach ($availableUsers as $idx => $userId) {
                    $submittedAt = now()->subHours(rand(1, 36))->subMinutes(rand(0, 59));

                    if ($pt->type === 'file_upload') {
                        $sc = $excelScenarios[$idx % count($excelScenarios)];

                        PostTestResult::create([
                            'user_id'             => $userId,
                            'post_test_id'        => $pt->id,
                            'score'               => 0,
                            'ai_score'            => null,
                            'final_score'         => null,
                            'answers'             => null,
                            'file_path'           => $sc['file_path'],
                            'file_name'           => $sc['file_name'],
                            'notes'               => $sc['notes'],
                            'is_passed'           => false,
                            'status'              => 'submitted',
                            'grading_status'      => PostTestResult::GRADING_PENDING,
                            'needs_manual_review' => false,
                            'completed_at'        => $submittedAt,
                        ]);
                        $totalCreated++;
                    } elseif ($pt->type === 'essay') {
                        $essayData = $this->buildEssayScenario($idx, $courseName, $pt->title);

                        PostTestResult::create([
                            'user_id'             => $userId,
                            'post_test_id'        => $pt->id,
                            'score'               => 0,
                            'ai_score'            => null,
                            'final_score'         => null,
                            'answers'             => $essayData['answers'],
                            'file_path'           => null,
                            'file_name'           => null,
                            'notes'               => $essayData['notes'],
                            'is_passed'           => false,
                            'status'              => 'submitted',
                            'grading_status'      => PostTestResult::GRADING_PENDING,
                            'needs_manual_review' => false,
                            'completed_at'        => $submittedAt,
                        ]);
                        $totalCreated++;
                    }
                }
            }
        }

        $this->command?->info("OngoingEvaluationDemoSeeder selesai: {$totalCreated} pengumpulan PENDING baru ditambahkan pada seluruh event Sedang Berlangsung.");
    }

    /**
     * Variasi kualitas jawaban uraian agar hasil penilaian otomatis (AI + Local Pre-Filter) realistis saat presentasi.
     */
    private function buildEssayScenario(int $idx, string $courseName, string $evalTitle): array
    {
        $topic = trim($courseName);

        $scenarios = [
            // 0: Sangat Lengkap (5/5 poin kunci di kedua soal -> estimasi 92-100)
            [
                'answers' => [
                    "Implementasi {$topic} di instansi diawali dengan pemetaan regulasi nasional dan daerah sebagai dasar hukum operasional, dilanjutkan pengumpulan data primer langsung dari unit kerja dan pelaku usaha. Selanjutnya dilakukan analisis kebutuhan kompetensi serta ketersediaan SDM, diikuti validasi indikator kinerja bersama tim verifikator agar hasil proyeksi tenaga kerja tersusun akurat dan dapat dipertanggungjawabkan.",
                    "Untuk mengatasi kendala lapangan pada studi kasus {$evalTitle}, langkah mitigasi utama mencakup sinkronisasi data antar-aplikasi secara berkala, penguatan koordinasi lintas unit teknis dan perencana, serta standardisasi SOP pelaporan. Selain itu perlu diterapkan audit kepatuhan secara periodik sebagai bagian dari mitigasi risiko agar deviasi target dapat dideteksi lebih dini.",
                ],
                'notes' => 'Jawaban disusun berdasarkan praktik implementasi di dinas/unit kerja kami.',
            ],
            // 1: Sangat Lengkap dengan perspektif operasional (5/5 poin kunci -> estimasi 90-98)
            [
                'answers' => [
                    "Langkah strategis penerapan {$topic} dimulai dari pemetaan regulasi ketenagakerjaan yang berlaku agar tidak terjadi tumpang tindih kewenangan. Tahap berikutnya adalah pengumpulan data primer melalui survei terstruktur, kemudian menjalankan analisis kebutuhan secara sektoral. Sebelum disahkan, seluruh temuan wajib melewati validasi indikator untuk menghasilkan proyeksi tenaga kerja jangka menengah yang presisi.",
                    "Berdasarkan evaluasi komparatif studi kasus, hambatan utama adalah fragmentasi basis data. Rekomendasi perbaikan meliputi sinkronisasi data terpusat, forum koordinasi lintas unit setiap triwulan, penyusunan standardisasi SOP yang seragam, pelaksanaan audit kepatuhan internal, serta penyusunan peta mitigasi risiko operasional secara berkelanjutan.",
                ],
                'notes' => 'Mohon umpan balik terkait langkah validasi indikator di daerah.',
            ],
            // 2: Baik (4/5 poin kunci -> estimasi 80-88)
            [
                'answers' => [
                    "Dalam menerapkan {$topic}, unit kami memprioritaskan pemetaan regulasi terlebih dahulu sebagai acuan kebijakan. Setelah itu dilakukan pengumpulan data primer dari lapangan dan analisis kebutuhan tenaga kerja pada sektor prioritas, lalu dilakukan validasi indikator bersama pimpinan unit sebelum menyusun rencana aksi tahunan.",
                    "Solusi atas kendala di lapangan difokuskan pada sinkronisasi data antarbidang dan peningkatan koordinasi lintas unit agar tidak ada duplikasi kegiatan. Kami juga menyusun standardisasi SOP alur kerja serta menerapkan langkah mitigasi risiko terhadap keterlambatan pelaporan.",
                ],
                'notes' => 'Disusun sesuai alur kerja di bidang perencanaan kami.',
            ],
            // 3: Baik (4/5 poin kunci -> estimasi 78-86)
            [
                'answers' => [
                    "Metodologi implementasi {$topic} dilakukan secara bertahap mulai dari pengumpulan data primer yang sahih, analisis kebutuhan berdasarkan tren sektoral, serta validasi indikator utama. Seluruh tahapan tersebut diarahkan untuk menyusun proyeksi tenaga kerja daerah yang selaras dengan target RPJMD.",
                    "Rekomendasi penanganan kendala meliputi koordinasi lintas unit secara terjadwal, standardisasi SOP verifikasi berkas, pelaksanaan audit kepatuhan secara rutin setiap semester, dan penerapan mitigasi risiko pada titik rawan kesalahan input.",
                ],
                'notes' => null,
            ],
            // 4: Cukup / Sekitar KKM (3/5 poin kunci -> estimasi 70-78)
            [
                'answers' => [
                    "Penerapan {$topic} dilakukan dengan pemetaan regulasi terkait dan pengumpulan data primer dari instansi terkait. Setelah data terkumpul, tim melakukan analisis kebutuhan untuk menentukan program prioritas di tahun berjalan.",
                    "Kendala lapangan dapat dikurangi melalui sinkronisasi data yang lebih rapi serta koordinasi lintas unit yang rutin agar semua pihak memahami tanggung jawabnya sesuai SOP yang berlaku.",
                ],
                'notes' => 'Jawaban ringkas sesuai poin utama sesi paparan.',
            ],
            // 5: Borderline / Cukup (3/5 poin kunci -> estimasi 68-76, bagus untuk demo Triage / Borderline!)
            [
                'answers' => [
                    "Untuk menjalankan {$topic}, langkah awalnya adalah pengumpulan data primer dari perusahaan dan unit teknis, kemudian dilakukan validasi indikator agar data tidak keliru sebelum dipakai untuk menyusun proyeksi tenaga kerja.",
                    "Upaya perbaikan yang disarankan yaitu memperkuat koordinasi lintas unit serta melakukan audit kepatuhan berkala supaya pelaksanaan kegiatan tetap sesuai ketentuan.",
                ],
                'notes' => 'Mohon arahan apabila masih ada tahapan metodologi yang terlewat.',
            ],
            // 6: Kurang Lengkap (1-2/5 poin kunci -> estimasi 45-62, di bawah KKM)
            [
                'answers' => [
                    "Pelaksanaan {$topic} di instansi kami dilakukan dengan rapat internal dan mengumpulkan laporan berkala dari staf operasional sesuai jadwal yang ditetapkan pimpinan.",
                    "Kendala yang sering terjadi biasanya karena keterbatasan waktu dan SDM, sehingga solusinya perlu menambah sosialisasi dan koordinasi lintas unit secara informal.",
                ],
                'notes' => 'Dikerjakan singkat di sela tugas dinas.',
            ],
            // 7: Sangat Singkat (< 15 karakter -> langsung tertangkap LOCAL_PREFILTER 0 kuota AI!)
            [
                'answers' => [
                    'Sudah jelas.',
                    '',
                ],
                'notes' => 'Terkirim otomatis sebelum selesai mengetik.',
            ],
        ];

        return $scenarios[$idx % count($scenarios)];
    }
}
