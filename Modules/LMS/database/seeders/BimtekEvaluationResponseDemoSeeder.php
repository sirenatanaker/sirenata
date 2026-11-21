<?php

namespace Modules\LMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\LMS\Models\BimtekEvaluationAnswer;
use Modules\LMS\Models\BimtekEvaluationQuestion;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\BimtekInstructorEvaluation;

class BimtekEvaluationResponseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $questions = BimtekEvaluationQuestion::where('is_active', true)
            ->orderBy('order')
            ->get();

        if ($questions->isEmpty()) {
            $defaults = [
                [
                    'question'    => 'Penguasaan Materi & Kejelasan Paparan Narasumber',
                    'description' => 'Sejauh mana narasumber menguasai substansi materi dan menyampaikannya secara runtut serta mudah dipahami.',
                    'type'        => 'scale',
                    'scale_max'   => 5,
                    'min_label'   => 'Sangat Kurang',
                    'max_label'   => 'Sangat Baik',
                    'order'       => 1,
                ],
                [
                    'question'    => 'Metode Penyampaian, Interaktivitas, & Diskusi Sesi',
                    'description' => 'Kualitas interaksi dua arah, studi kasus, dan respons narasumber terhadap pertanyaan peserta.',
                    'type'        => 'scale',
                    'scale_max'   => 5,
                    'min_label'   => 'Sangat Kurang',
                    'max_label'   => 'Sangat Baik',
                    'order'       => 2,
                ],
                [
                    'question'    => 'Ketepatan Waktu & Fasilitasi Jadwal Bimtek',
                    'description' => 'Kesesuaian pelaksanaan sesi Live Zoom dan evaluasi harian dengan jadwal yang telah ditetapkan.',
                    'type'        => 'scale',
                    'scale_max'   => 5,
                    'min_label'   => 'Sangat Kurang',
                    'max_label'   => 'Sangat Baik',
                    'order'       => 3,
                ],
                [
                    'question'    => 'Kualitas Sarana Pendukung & Layanan Fasilitator Bimtek',
                    'description' => 'Kenyamanan platform e-learning, kejelasan panduan, serta kesiapan fasilitator membantu peserta.',
                    'type'        => 'scale',
                    'scale_max'   => 5,
                    'min_label'   => 'Sangat Kurang',
                    'max_label'   => 'Sangat Baik',
                    'order'       => 4,
                ],
                [
                    'question'    => 'Catatan, Masukan, dan Saran untuk Narasumber & Panitia Penyelenggara',
                    'description' => 'Tuliskan apresiasi maupun saran perbaikan untuk pelaksanaan bimtek berikutnya.',
                    'type'        => 'text',
                    'scale_max'   => 5,
                    'min_label'   => null,
                    'max_label'   => null,
                    'order'       => 5,
                ],
            ];

            foreach ($defaults as $item) {
                BimtekEvaluationQuestion::firstOrCreate(
                    ['question' => $item['question']],
                    array_merge($item, [
                        'section'     => 'Umum',
                        'is_required' => $item['type'] === 'scale',
                        'is_active'   => true,
                    ])
                );
            }

            $questions = BimtekEvaluationQuestion::where('is_active', true)
                ->orderBy('order')
                ->get();
        }

        $scaleQuestions = $questions->where('type', 'scale')->values();
        $textQuestions = $questions->where('type', 'text')->values();

        $feedbackPool = [
            'Penyampaian materi sangat sistematis dan studi kasus yang dibahas relevan langsung dengan kondisi di dinas kami. Mohon modul paparan dapat diunduh sebelum sesi dimulai.',
            'Narasumber sangat menguasai substansi dan sabar menjawab pertanyaan teknis saat sesi diskusi Zoom. Alur evaluasi harian di LMS juga mudah diikuti.',
            'Pelaksanaan bimtek berjalan tepat waktu dan tertib. Saran ke depan agar durasi sesi tanya jawab praktik lembar kerja Excel bisa ditambah 15–20 menit.',
            'Fasilitator sangat responsif membantu peserta ketika ada kendala teknis presensi maupun unggah berkas tugas. Secara keseluruhan sangat memuaskan.',
            'Materi sudah sangat baik dan aplikatif, terutama saat pembahasan simulasi indikator daerah. Akan lebih bagus lagi bila disertai contoh studi kasus provinsi dan kab/kota yang lebih beragam.',
            'Platform LMS nyaman digunakan baik dari laptop maupun HP, dan jadwal sesi Live Zoom sangat jelas. Terima kasih kepada narasumber dan tim panitia.',
            'Pembahasan regulasi terbaru sangat membantu kami menyusun perencanaan di unit kerja. Harapannya tersedia rekaman sesi bagi peserta yang sempat terkendala jaringan.',
            'Interaksi pada sesi diskusi sangat hidup dan terbuka. Penjelasan mengenai metode analisis mudah dipahami meskipun bagi peserta pemula.',
            'Kualitas audio dan tampilan presentasi narasumber jernih serta rapi. Mohon ke depan jadwal evaluasi akhir diberi jeda sedikit lebih longgar bagi peserta daerah.',
            'Sangat bermanfaat untuk peningkatan kapasitas ASN di bidang ketenagakerjaan. Penilaian tugas yang cepat membuat kami langsung mengetahui bagian mana yang perlu diperbaiki.',
            'Susunan materi dari hari pertama sampai hari ketiga saling berkesinambungan. Fasilitator aktif mengingatkan jadwal sesi sehingga peserta tidak tertinggal.',
            'Sesi praktik studi kasus menjadi bagian paling menarik. Saran agar disediakan forum konsultasi singkat setelah bimtek selesai.',
            'Materi padat namun mudah dicerna karena banyak ilustrasi nyata di lapangan. Layanan panitia sangat ramah dan profesional.',
            'Secara umum penyelenggaraan sudah sangat baik, tepat waktu, dan terstruktur. Semoga pelatihan lanjutan untuk tingkat lanjutan segera dibuka.',
            'Narasumber memberikan contoh implementasi yang konkret sehingga langsung bisa diterapkan pada penyusunan dokumen kerja di instansi kami.',
        ];

        // Variasi profil rating (1-5) agar rata-rata aspek terlihat alami (~4.3 - 4.7)
        $ratingProfiles = [
            [5, 5, 5, 5],
            [5, 5, 4, 5],
            [5, 4, 5, 5],
            [4, 5, 5, 4],
            [5, 4, 4, 5],
            [4, 4, 5, 5],
            [4, 4, 4, 5],
            [5, 5, 4, 4],
            [4, 3, 4, 4],
            [5, 4, 5, 4],
            [4, 5, 4, 4],
            [3, 4, 4, 4],
        ];

        $events = BimtekEvent::with('course')->orderBy('start_date')->get();
        $createdCount = 0;

        foreach ($events as $event) {
            if (!$event->course_id) {
                continue;
            }

            $statusLabel = $event->status_badge['label'] ?? '';
            if (!in_array($statusLabel, ['Selesai', 'Sedang Berlangsung', 'Masa Evaluasi'], true)) {
                continue;
            }

            $enrolledUserIds = DB::table('course_student')
                ->where('bimtek_event_id', $event->id)
                ->pluck('user_id');

            if ($enrolledUserIds->isEmpty()) {
                continue;
            }

            $targetRespondents = $statusLabel === 'Selesai'
                ? min(18, $enrolledUserIds->count())
                : min(10, $enrolledUserIds->count());

            $existingUserIds = BimtekInstructorEvaluation::where('bimtek_event_id', $event->id)
                ->pluck('user_id');

            $needed = max(0, $targetRespondents - $existingUserIds->count());
            if ($needed === 0) {
                continue;
            }

            $candidates = $enrolledUserIds->diff($existingUserIds)->values()->take($needed);

            foreach ($candidates as $idx => $userId) {
                $profile = $ratingProfiles[($createdCount + $idx) % count($ratingProfiles)];
                $feedback = $feedbackPool[($createdCount + $idx) % count($feedbackPool)];
                $submittedAt = now()->subDays(rand(0, 6))->subHours(rand(1, 18))->subMinutes(rand(0, 59));

                $scaleRatings = [];
                foreach ($scaleQuestions as $qIdx => $sq) {
                    $scaleRatings[$sq->id] = $profile[$qIdx % count($profile)];
                }

                $avgScore = count($scaleRatings) > 0
                    ? round(array_sum($scaleRatings) / count($scaleRatings), 2)
                    : 4.5;

                $evaluation = BimtekInstructorEvaluation::create([
                    'bimtek_event_id'    => $event->id,
                    'course_id'          => $event->course_id,
                    'user_id'            => $userId,
                    'rating_materi'      => $profile[0],
                    'rating_penyampaian' => $profile[1],
                    'rating_fasilitasi'  => $profile[2],
                    'overall_score'      => $avgScore,
                    'feedback'           => $feedback,
                    'created_at'         => $submittedAt,
                    'updated_at'         => $submittedAt,
                ]);

                foreach ($scaleQuestions as $sq) {
                    BimtekEvaluationAnswer::create([
                        'evaluation_id' => $evaluation->id,
                        'question_id'   => $sq->id,
                        'rating'        => $scaleRatings[$sq->id],
                        'text_answer'   => null,
                        'created_at'    => $submittedAt,
                        'updated_at'    => $submittedAt,
                    ]);
                }

                foreach ($textQuestions as $tq) {
                    BimtekEvaluationAnswer::create([
                        'evaluation_id' => $evaluation->id,
                        'question_id'   => $tq->id,
                        'rating'        => null,
                        'text_answer'   => $feedback,
                        'created_at'    => $submittedAt,
                        'updated_at'    => $submittedAt,
                    ]);
                }

                $createdCount++;
            }
        }

        $this->command?->info("BimtekEvaluationResponseDemoSeeder selesai: {$createdCount} respon kuesioner evaluasi berhasil ditambahkan.");
    }
}
