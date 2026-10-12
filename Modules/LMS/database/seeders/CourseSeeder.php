<?php

namespace Modules\LMS\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\LMS\Models\Category;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\StudentContentProgress;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Total 12 Kursus: 6 Terdaftar (Kursus Saya) & 6 Katalog
        $courseData = [
            // --- 6 KURSUS TERDAFTAR (KURSUS SAYA) ---
            [
                'name' => 'Perencanaan Tenaga Kerja Makro',
                'category' => 'Perencanaan',
                'description' => 'Mempelajari metodologi penyusunan rencana tenaga kerja makro pada level nasional dan daerah dengan indikator demografi dan ketenagakerjaan.',
                'is_enrolled' => true,
            ],
            [
                'name' => 'Perencanaan Tenaga Kerja Mikro',
                'category' => 'Praktik',
                'description' => 'Panduan teknis analisis kebutuhan dan ketersediaan tenaga kerja pada tingkat unit kerja dan instansi/perusahaan.',
                'is_enrolled' => true,
            ],
            [
                'name' => 'Indeks Pembangunan Ketenagakerjaan (IPK)',
                'category' => 'Perkiraan',
                'description' => 'Kajian mendalam mengenai pengukuran performa indikator pembangunan ketenagakerjaan secara komprehensif.',
                'is_enrolled' => true,
            ],
            [
                'name' => 'Implementasi Sistem Merit dalam Manajemen ASN',
                'category' => 'Perpres',
                'description' => 'Penerapan kualifikasi, kompetensi, dan kinerja secara adil dalam pengembangan karier aparatur sipil negara.',
                'is_enrolled' => true,
            ],
            [
                'name' => 'Teknik Proyeksi Penduduk dan Angkatan Kerja',
                'category' => 'Teori',
                'description' => 'Belajar menghitung laju pertumbuhan penduduk, partisipasi angkatan kerja, dan proyeksi ketenagakerjaan masa depan.',
                'is_enrolled' => true,
            ],
            [
                'name' => 'Audit dan Pengawasan Ketenagakerjaan Sektoral',
                'category' => 'Praktik',
                'description' => 'Standar operasional prosedur pengawasan norma kerja, Keselamatan dan Kesehatan Kerja (K3) di berbagai sektor.',
                'is_enrolled' => true,
            ],

            // --- 6 KURSUS KATALOG (BELUM TERDAFTAR) ---
            [
                'name' => 'Analisis Kebutuhan Pelatihan Kerja',
                'category' => 'Praktik',
                'description' => 'Metodologi identifikasi gap kompetensi dan perencanaan diklat pegawai berbasis kebutuhan riil industri.',
                'is_enrolled' => false,
            ],
            [
                'name' => 'Regulasi dan Kebijakan Ketenagakerjaan Terkini',
                'category' => 'Perpres',
                'description' => 'Tinjauan yuridis peraturan pemerintah dan instrumen kepatuhan hukum ketenagakerjaan di Indonesia.',
                'is_enrolled' => false,
            ],
            [
                'name' => 'Optimalisasi Sistem Informasi Pasar Kerja (SIPKerja)',
                'category' => 'Perencanaan',
                'description' => 'Teknik pemanfaatan platform digital ketenagakerjaan untuk mempertemukan pencari kerja dan pemberi kerja.',
                'is_enrolled' => false,
            ],
            [
                'name' => 'Manajemen Produktivitas Tenaga Kerja Nasional',
                'category' => 'Teori',
                'description' => 'Strategi peningkatan daya saing dan produktivitas tenaga kerja melalui program vokasi serta pelatihan bersertifikat.',
                'is_enrolled' => false,
            ],
            [
                'name' => 'Pengelolaan Big Data Ketenagakerjaan',
                'category' => 'Perkiraan',
                'description' => 'Pemanfaatan data analitik modern guna merumuskan kebijakan intervensi pasar kerja yang akurat dan tepat sasaran.',
                'is_enrolled' => false,
            ],
            [
                'name' => 'Evaluasi Kinerja Program Pelatihan Vokasi',
                'category' => 'Praktik',
                'description' => 'Metode pengukuran dampak dan efektivitas penyelenggaraan balai latihan kerja terhadap penyerapan tenaga kerja.',
                'is_enrolled' => false,
            ],
        ];

        // 4 Palet Warna Utama Sesuai Permintaan
        $brandColors = [
            '13416B', // Navy
            '547996', // Slate Blue
            '8BB1CC', // Light Blue
            '79A736', // Muted Green
        ];

        foreach ($courseData as $index => $item) {
            $cat = Category::where('name', $item['category'])->first() ?? Category::first();
            $bgColor = $brandColors[$index % count($brandColors)];

            // Ambil maksimal 2 kata pertama agar inisial UI Avatars bersih
            $words = explode(' ', trim($item['name']));
            $safeName = $words[0] . ' ' . ($words[1] ?? '');

            Course::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'category_id' => $cat?->id,
                    'name'        => $item['name'],
                    'thumbnail'   => 'https://ui-avatars.com/api/?name=' . urlencode($safeName) . '&background=' . $bgColor . '&color=fff&size=512&bold=true&length=2',
                    'description' => $item['description'],
                ]
            );
        }

        // 2. Logika Pendaftaran (Enrollment) User — HANYA local/testing.
        // Berisi progres acak & mentor dummy, jangan pernah jalan di production.
        if (! app()->environment('local', 'testing')) {
            $this->command->info('CourseSeeder: katalog diperbarui, enrollment dilewati (production).');

            return;
        }

        $courses = Course::with(['sections.contents'])->get();

        $users = User::role('user')->get();
        if ($users->isEmpty()) {
            $users = User::all();
        }

        if ($users->isNotEmpty()) {
            foreach ($courses as $course) {
                // Assign Mentor
                $mentors = $users->take(1);
                foreach ($mentors as $mentor) {
                    $course->mentors()->syncWithoutDetaching([
                        $mentor->id => [
                            'is_active'  => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ]);
                }

                $isEnrolledTarget = collect($courseData)->firstWhere('name', $course->name)['is_enrolled'] ?? false;

                // Hanya daftarkan user jika kursus tersebut ditandai is_enrolled = true (Kursus Saya)
                if ($isEnrolledTarget) {
                    $allContents   = $course->sections->flatMap->contents;
                    $totalContents = $allContents->count();

                    foreach ($users as $student) {
                        $completedCount = $totalContents > 0 ? rand(0, $totalContents) : 0;
                        $progress = $totalContents > 0 ? (int) round(($completedCount / $totalContents) * 100) : 0;

                        $status = match (true) {
                            $progress === 0 => 'enrolled',
                            $progress < 100 => 'in_progress',
                            default         => 'completed',
                        };

                        $course->students()->syncWithoutDetaching([
                            $student->id => [
                                'status'       => $status,
                                'progress'     => $progress,
                                'completed_at' => $progress === 100 ? now() : null,
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ]
                        ]);

                        if ($totalContents > 0 && $completedCount > 0) {
                            $contentsToComplete = $allContents->shuffle()->take($completedCount);
                            foreach ($contentsToComplete as $content) {
                                StudentContentProgress::firstOrCreate(
                                    [
                                        'user_id'            => $student->id,
                                        'section_content_id' => $content->id,
                                    ],
                                    [
                                        'completed_at' => now(),
                                        'created_at'   => now(),
                                        'updated_at'   => now(),
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }

        $this->command->info("CourseSeeder berhasil memperbarui 6 kursus terdaftar dan 6 kursus katalog dengan 4 palet warna utama! 🚀");
    }
}