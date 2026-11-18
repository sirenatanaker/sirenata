<?php

namespace Modules\LMS\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\Category;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\StudentContentProgress;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan kategori dasar tersedia
        $this->call(CategorySeeder::class);

        // 16 Kursus Realistis: 4 Kursus Saya (Enrolled) & 12 Katalog (Un-enrolled)
        $courseData = [
            // =========================================================================
            // 4 KURSUS SAYA (TERDAFTAR)
            // =========================================================================
            [
                'name'        => 'Perencanaan Tenaga Kerja Makro',
                'category'    => 'Perencanaan',
                'description' => 'Mempelajari metodologi penyusunan rencana tenaga kerja makro pada level nasional dan daerah dengan indikator demografi dan ketenagakerjaan komprehensif.',
                'is_enrolled' => true,
                'target_pct'  => 40,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Online Perencanaan Makro Angkatan I 2026',
                    'batch_code'            => 'PTK-MAKRO-2026-I',
                    'execution_type'        => 'online',
                    'status'                => 'ongoing',
                    'start_date'            => now()->subDays(3)->setTime(8, 30),
                    'end_date'              => now()->addDays(12)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(14)->setTime(8, 0),
                    'registration_end_at'   => now()->subDays(4)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/1234567890',
                    'zoom_meeting_id'       => '891 2345 6789',
                    'zoom_passcode'         => 'MAKRO2026',
                    'zoom_attendance_code'  => 'MAKRO99',
                    'zoom_scheduled_at'     => now()->subDays(1)->setTime(9, 0),
                    'zoom_end_at'           => now()->subDays(1)->setTime(12, 0),
                    'quota'                 => 60,
                    'description'           => 'Pelatihan intensif penyusunan dokumen perencanaan tenaga kerja makro lima tahunan.',
                ],
            ],
            [
                'name'        => 'Indeks Pembangunan Ketenagakerjaan (IPK)',
                'category'    => 'Perkiraan',
                'description' => 'Kajian mendalam mengenai pengukuran performa indikator pembangunan ketenagakerjaan secara komprehensif antarprovinsi dan kabupaten/kota.',
                'is_enrolled' => true,
                'target_pct'  => 20,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Pengukuran IPK Daerah Angkatan II',
                    'batch_code'            => 'IPK-DAERAH-2026-II',
                    'execution_type'        => 'online',
                    'status'                => 'ongoing',
                    'start_date'            => now()->subDays(2)->setTime(8, 30),
                    'end_date'              => now()->addDays(10)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(12)->setTime(8, 0),
                    'registration_end_at'   => now()->subDays(3)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/9876543210',
                    'zoom_meeting_id'       => '987 6543 2100',
                    'zoom_passcode'         => 'IPK2026',
                    'zoom_attendance_code'  => 'IPK88',
                    'zoom_scheduled_at'     => now()->addDays(2)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(2)->setTime(11, 30),
                    'quota'                 => 50,
                    'description'           => 'Panduan pengukuran 9 indikator utama penyusun Indeks Pembangunan Ketenagakerjaan.',
                ],
            ],
            [
                'name'        => 'Implementasi Sistem Merit dalam Manajemen ASN',
                'category'    => 'Perpres',
                'description' => 'Penerapan kualifikasi, kompetensi, dan kinerja secara adil dalam pengembangan karier dan penataan talenta aparatur sipil negara.',
                'is_enrolled' => true,
                'target_pct'  => 0,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Sistem Merit & Manajemen Talenta ASN 2026',
                    'batch_code'            => 'MERIT-ASN-2026',
                    'execution_type'        => 'online',
                    'status'                => 'open_registration',
                    'start_date'            => now()->addDays(7)->setTime(8, 30),
                    'end_date'              => now()->addDays(21)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(3)->setTime(8, 0),
                    'registration_end_at'   => now()->addDays(6)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/5551234567',
                    'zoom_meeting_id'       => '555 1234 5678',
                    'zoom_passcode'         => 'MERIT26',
                    'zoom_attendance_code'  => 'MERIT77',
                    'zoom_scheduled_at'     => now()->addDays(9)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(9)->setTime(12, 0),
                    'quota'                 => 75,
                    'description'           => 'Sosialisasi dan asistensi penerapan 8 aspek sistem merit di instansi pemerintah daerah.',
                ],
            ],
            [
                'name'        => 'Audit dan Pengawasan Ketenagakerjaan Sektoral',
                'category'    => 'Praktik',
                'description' => 'Standar operasional prosedur pengawasan norma kerja, Keselamatan dan Kesehatan Kerja (K3) serta penegakan hukum ketenagakerjaan di berbagai sektor.',
                'is_enrolled' => true,
                'target_pct'  => 100, // Selesai / Lulus
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Pengawasan Ketenagakerjaan & K3 Sektoral 2026',
                    'batch_code'            => 'AUDIT-K3-2026-I',
                    'execution_type'        => 'online',
                    'status'                => 'completed',
                    'start_date'            => now()->subDays(20)->setTime(8, 30),
                    'end_date'              => now()->subDays(5)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(30)->setTime(8, 0),
                    'registration_end_at'   => now()->subDays(21)->setTime(23, 59),
                    'assessment_start_at'   => now()->subDays(5)->setTime(16, 0),
                    'assessment_end_at'     => now()->subDays(2)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/9998887776',
                    'zoom_meeting_id'       => '999 8887 7776',
                    'zoom_passcode'         => 'AUDIT2026',
                    'quota'                 => 50,
                    'description'           => 'Pelatihan pengawasan norma kerja dan SMK3 sektor industri manufaktur dan jasa.',
                ],
            ],

            // =========================================================================
            // 12 KURSUS KATALOG (BELUM TERDAFTAR / SIAP DAFTAR)
            // =========================================================================

            // --- 6 Kursus Bimtek Online (Open Registration) ---
            [
                'name'        => 'Analisis Kebutuhan Pelatihan Kerja (AKPK)',
                'category'    => 'Praktik',
                'description' => 'Metodologi identifikasi gap kompetensi dan perencanaan diklat pegawai berbasis kebutuhan riil industri dan standar kompetensi kerja.',
                'is_enrolled' => false,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek AKPK Berbasis SKKNI Gelombang 1',
                    'batch_code'            => 'AKPK-B1-2026',
                    'execution_type'        => 'online',
                    'status'                => 'open_registration',
                    'start_date'            => now()->addDays(10)->setTime(8, 30),
                    'end_date'              => now()->addDays(25)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(2)->setTime(8, 0),
                    'registration_end_at'   => now()->addDays(9)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/1112223334',
                    'zoom_meeting_id'       => '111 2223 3344',
                    'zoom_passcode'         => 'AKPK2026',
                    'zoom_attendance_code'  => 'AKPK11',
                    'zoom_scheduled_at'     => now()->addDays(12)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(12)->setTime(12, 0),
                    'quota'                 => 50,
                    'description'           => 'Pelatihan teknis analisis gap kompetensi aparatur ketenagakerjaan di balai pelatihan.',
                ],
            ],
            [
                'name'        => 'Penyusunan Rencana Tenaga Kerja Daerah (RTKD)',
                'category'    => 'Perencanaan',
                'description' => 'Panduan perumusan dokumen rencana tenaga kerja daerah provinsi dan kab/kota berdasar analisis data demografi dan tren investasi lokal.',
                'is_enrolled' => false,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Penyusunan RTK Provinsi & Kabupaten/Kota 2026',
                    'batch_code'            => 'RTKD-2026-I',
                    'execution_type'        => 'online',
                    'status'                => 'open_registration',
                    'start_date'            => now()->addDays(12)->setTime(8, 30),
                    'end_date'              => now()->addDays(27)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(1)->setTime(8, 0),
                    'registration_end_at'   => now()->addDays(11)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/2223334445',
                    'zoom_meeting_id'       => '222 3334 4455',
                    'zoom_passcode'         => 'RTKD2026',
                    'zoom_attendance_code'  => 'RTKD22',
                    'zoom_scheduled_at'     => now()->addDays(14)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(14)->setTime(12, 0),
                    'quota'                 => 40,
                    'description'           => 'Asistensi teknis penyusunan proyeksi kesempatan kerja sektoral bagi Bappeda & Disnaker.',
                ],
            ],
            [
                'name'        => 'Penerapan Sistem Manajemen K3 (SMK3) PP 50/2012',
                'category'    => 'Praktik',
                'description' => 'Standar implementasi, pengawasan keselamatan kerja, audit kepatuhan SMK3, serta mitigasi risiko bahaya di lingkungan kerja.',
                'is_enrolled' => false,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Auditor Internal SMK3 Angkatan III',
                    'batch_code'            => 'SMK3-A3-2026',
                    'execution_type'        => 'online',
                    'status'                => 'open_registration',
                    'start_date'            => now()->addDays(15)->setTime(8, 30),
                    'end_date'              => now()->addDays(30)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(4)->setTime(8, 0),
                    'registration_end_at'   => now()->addDays(14)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/3334445556',
                    'zoom_meeting_id'       => '333 4445 5566',
                    'zoom_passcode'         => 'SMK32026',
                    'zoom_attendance_code'  => 'SMK333',
                    'zoom_scheduled_at'     => now()->addDays(17)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(17)->setTime(12, 0),
                    'quota'                 => 60,
                    'description'           => 'Pembekalan sertifikasi kompetensi auditor internal sistem manajemen keselamatan dan kesehatan kerja.',
                ],
            ],
            [
                'name'        => 'Optimalisasi Sistem Informasi Pasar Kerja (SIPKerja)',
                'category'    => 'Perencanaan',
                'description' => 'Strategi pemanfaatan ekosistem platform pasar kerja daring guna mempertemukan pasokan pencari kerja dan lowongan industri secara presisi.',
                'is_enrolled' => false,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Penguatan Layanan Digital Bursa Kerja 2026',
                    'batch_code'            => 'SIPKERJA-2026',
                    'execution_type'        => 'online',
                    'status'                => 'open_registration',
                    'start_date'            => now()->addDays(16)->setTime(8, 30),
                    'end_date'              => now()->addDays(30)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(2)->setTime(8, 0),
                    'registration_end_at'   => now()->addDays(15)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/4445556667',
                    'zoom_meeting_id'       => '444 5556 6677',
                    'zoom_passcode'         => 'SIPKERJA26',
                    'zoom_attendance_code'  => 'SIP44',
                    'zoom_scheduled_at'     => now()->addDays(18)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(18)->setTime(12, 0),
                    'quota'                 => 45,
                    'description'           => 'Penguatan kapasitas pengantar kerja dalam memvalidasi data lowongan dan konseling karier digital.',
                ],
            ],
            [
                'name'        => 'Kepatuhan Hukum Ketenagakerjaan & Hubungan Industrial',
                'category'    => 'Perpres',
                'description' => 'Tinjauan yuridis regulasi ketenagakerjaan terkini, perikatan Perjanjian Kerja Bersama (PKB), dan teknik pencegahan perselisihan industrial.',
                'is_enrolled' => false,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Mediasi & Penyelesaian Perselisihan Kerja Gelombang 2',
                    'batch_code'            => 'HKM-HI-B2',
                    'execution_type'        => 'online',
                    'status'                => 'open_registration',
                    'start_date'            => now()->addDays(18)->setTime(8, 30),
                    'end_date'              => now()->addDays(32)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(3)->setTime(8, 0),
                    'registration_end_at'   => now()->addDays(17)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/5556667778',
                    'zoom_meeting_id'       => '555 6667 7788',
                    'zoom_passcode'         => 'HKMHI2026',
                    'zoom_attendance_code'  => 'HI55',
                    'zoom_scheduled_at'     => now()->addDays(20)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(20)->setTime(12, 0),
                    'quota'                 => null,
                    'description'           => 'Teknik mediasi, konsiliasi, dan penyusunan anjuran hubungan industrial sesuai UU Ketenagakerjaan.',
                ],
            ],
            [
                'name'        => 'Tata Kelola Perlindungan Pekerja Migran Indonesia (PPMI)',
                'category'    => 'Perpres',
                'description' => 'Mekanisme penempatan, perlindungan hukum, dan jaminan sosial bagi tenaga kerja Indonesia di luar negeri sesuai mandat UU No. 18 Tahun 2017.',
                'is_enrolled' => false,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Penguatan Layanan Terpadu Satu Atap (LTSA) PPMI',
                    'batch_code'            => 'PPMI-LTSA-2026',
                    'execution_type'        => 'online',
                    'status'                => 'open_registration',
                    'start_date'            => now()->addDays(20)->setTime(8, 30),
                    'end_date'              => now()->addDays(35)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(5)->setTime(8, 0),
                    'registration_end_at'   => now()->addDays(19)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/6667778889',
                    'zoom_meeting_id'       => '666 7778 8899',
                    'zoom_passcode'         => 'PPMI2026',
                    'zoom_attendance_code'  => 'PPMI66',
                    'zoom_scheduled_at'     => now()->addDays(22)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(22)->setTime(12, 0),
                    'quota'                 => 35,
                    'description'           => 'Standarisasi layanan terpadu dan pencegahan tindak pidana perdagangan orang (TPPO) bagi CPMI.',
                ],
            ],

            // --- 3 Kursus Bimtek Online (Sedang Berlangsung) & 3 Kursus Mandiri / Self-Paced ---
            [
                'name'        => 'Manajemen Produktivitas Tenaga Kerja Nasional',
                'category'    => 'Teori',
                'description' => 'Konsep dan teknik pengukuran efisiensi jam kerja, Total Factor Productivity (TFP), dan strategi peningkatan daya saing SDM berstandar global.',
                'is_enrolled' => false,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Pengukuran Produktivitas Nasional Gelombang 1',
                    'batch_code'            => 'PROD-NAS-2026-I',
                    'execution_type'        => 'online',
                    'status'                => 'ongoing',
                    'start_date'            => now()->subDays(3)->setTime(8, 30),
                    'end_date'              => now()->addDays(10)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(12)->setTime(8, 0),
                    'registration_end_at'   => now()->subDays(4)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/7771112223',
                    'zoom_meeting_id'       => '777 1112 2233',
                    'zoom_passcode'         => 'PROD2026',
                    'zoom_attendance_code'  => 'PROD77',
                    'zoom_scheduled_at'     => now()->addDays(1)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(1)->setTime(12, 0),
                    'quota'                 => 50,
                    'description'           => 'Bimbingan teknis penghitungan Total Factor Productivity sektor industri daerah.',
                ],
            ],
            [
                'name'        => 'Pengelolaan Big Data Ketenagakerjaan',
                'category'    => 'Perkiraan',
                'description' => 'Pengolahan dan pemodelan data analitik pasar kerja skala besar untuk perumusan intervensi kebijakan ketenagakerjaan berbasis bukti.',
                'is_enrolled' => false,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Analitik Big Data Ketenagakerjaan Gelombang 1',
                    'batch_code'            => 'BIGDATA-2026-I',
                    'execution_type'        => 'online',
                    'status'                => 'ongoing',
                    'start_date'            => now()->subDays(2)->setTime(8, 30),
                    'end_date'              => now()->addDays(12)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(10)->setTime(8, 0),
                    'registration_end_at'   => now()->subDays(3)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/8882223334',
                    'zoom_meeting_id'       => '888 2223 3344',
                    'zoom_passcode'         => 'BIGDATA26',
                    'zoom_attendance_code'  => 'BDATA88',
                    'zoom_scheduled_at'     => now()->addDays(2)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(2)->setTime(12, 0),
                    'quota'                 => 60,
                    'description'           => 'Implementasi analitik big data untuk pemetaan kebutuhan tenaga kerja daerah.',
                ],
            ],
            [
                'name'        => 'Teknik Investigasi Kecelakaan Kerja & Ergonomi Industri',
                'category'    => 'Praktik',
                'description' => 'Prosedur investigasi akar penyebab (root cause analysis) insiden kerja serta tata cara rekayasa ergonomi stasiun kerja ramah operator.',
                'is_enrolled' => false,
                'bimtek'      => [
                    'batch_name'            => 'Bimtek Investigasi Kecelakaan Kerja & Ergonomi Gelombang 1',
                    'batch_code'            => 'IKK-ERGO-2026-I',
                    'execution_type'        => 'online',
                    'status'                => 'ongoing',
                    'start_date'            => now()->subDays(1)->setTime(8, 30),
                    'end_date'              => now()->addDays(14)->setTime(16, 0),
                    'registration_start_at' => now()->subDays(9)->setTime(8, 0),
                    'registration_end_at'   => now()->subDays(2)->setTime(23, 59),
                    'zoom_url'              => 'https://zoom.us/j/9993334445',
                    'zoom_meeting_id'       => '999 3334 4455',
                    'zoom_passcode'         => 'ERGO2026',
                    'zoom_attendance_code'  => 'ERGO99',
                    'zoom_scheduled_at'     => now()->addDays(3)->setTime(9, 0),
                    'zoom_end_at'           => now()->addDays(3)->setTime(12, 0),
                    'quota'                 => 45,
                    'description'           => 'Pendalaman investigasi insiden kerja menggunakan metode root cause analysis.',
                ],
            ],
            [
                'name'        => 'Metodologi Analisis Pasar Kerja & Proyeksi Pengangguran',
                'category'    => 'Perkiraan',
                'description' => 'Teknik pemodelan ekonometrika proyeksi penyerapan tenaga kerja sektoral, rasio elastisitas kesempatan kerja, dan estimasi angka TPT.',
                'is_enrolled' => false,
                'bimtek'      => null,
            ],
            [
                'name'        => 'Kebijakan Pengupahan dan Struktur Skala Upah (SUSU)',
                'category'    => 'Teori',
                'description' => 'Panduan teknis perumusan struktur dan skala upah berkeadilan sesuai ketentuan regulasi dengan mempertimbangkan kompetensi dan kinerja.',
                'is_enrolled' => false,
                'bimtek'      => null,
            ],
            [
                'name'        => 'Pemberdayaan Tenaga Kerja Mandiri (TKM) Berkelanjutan',
                'category'    => 'Perencanaan',
                'description' => 'Pola inkubasi wirausaha, pembinaan modal usaha rintisan ketenagakerjaan, dan pendampingan kemandirian ekonomi kelompok masyarakat rentan.',
                'is_enrolled' => false,
                'bimtek'      => null,
            ],
        ];

        // 4 Palet Warna Utama untuk Thumbnail UI Avatars
        $brandColors = [
            '13416B', // Navy
            '547996', // Slate Blue
            '8BB1CC', // Light Blue
            '79A736', // Muted Green
        ];

        // 1. Simpan Data Kursus & Bimtek Events
        foreach ($courseData as $index => $item) {
            $cat = Category::where('name', $item['category'])->first() ?? Category::first();
            $bgColor = $brandColors[$index % count($brandColors)];

            $words = explode(' ', trim($item['name']));
            $safeName = $words[0] . ' ' . ($words[1] ?? '');

            $course = Course::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'category_id' => $cat?->id,
                    'name'        => $item['name'],
                    'thumbnail'   => 'https://ui-avatars.com/api/?name=' . urlencode($safeName) . '&background=' . $bgColor . '&color=fff&size=512&bold=true&length=2',
                    'description' => $item['description'],
                ]
            );

            // Buat atau perbarui Bimtek Event jika kursus ini memiliki program Bimtek
            if (!empty($item['bimtek'])) {
                BimtekEvent::updateOrCreate(
                    [
                        'batch_code' => $item['bimtek']['batch_code'],
                    ],
                    array_merge($item['bimtek'], [
                        'course_id' => $course->id,
                    ])
                );
            }
        }

        // 2. Jalankan Seeder Modul & Soal Evaluasi agar seluruh materi tersedia
        $this->call([
            CourseSectionSeeder::class,
            PostTestSeeder::class,
        ]);

        // 3. Logika Pendaftaran (Enrollment) User (Hanya local / testing)
        if (! app()->environment('local', 'testing')) {
            $this->command->info('CourseSeeder: katalog diperbarui, enrollment dilewati (production).');
            return;
        }

        $allCourses = Course::with(['sections.contents'])->get();
        $users = User::role('user')->get();
        if ($users->isEmpty()) {
            $users = User::all();
        }

        if ($users->isEmpty()) {
            $this->command->warn('Tidak ada user untuk di-enroll.');
            return;
        }

        // Mentor default untuk setiap kursus
        $firstUser = $users->first();
        foreach ($allCourses as $c) {
            $c->mentors()->syncWithoutDetaching([
                $firstUser->id => [
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Pastikan file dummy jawaban tugas peserta tersedia untuk link pratinjau admin
        Storage::disk('public')->makeDirectory('assignments/demo');
        if (!Storage::disk('public')->exists('assignments/demo/Contoh_Jawaban_Tugas_Peserta.pdf')) {
            Storage::disk('public')->put(
                'assignments/demo/Contoh_Jawaban_Tugas_Peserta.pdf',
                "%PDF-1.4\n1 0 obj\n<< /Title (Contoh Lembar Jawaban Tugas Peserta) /Author (Peserta Bimtek) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF"
            );
        }

        // Akun demo utama yang harus tetap belum terdaftar pada kursus katalog
        $primaryDemoEmails = [
            'pusat@gmail.com',
            'pusat2@gmail.com',
            'pusat3@gmail.com',
            'user.pusat@gmail.com',
            'user.jabar@gmail.com',
            'user.sumut@gmail.com',
            'user.kotabandung@gmail.com',
            'user.kabbandung@gmail.com',
            'user.kotamedan@gmail.com',
            'user.dki-jakarta@gmail.com',
            'user.jawa-barat@gmail.com',
            'user.sumatera-utara@gmail.com',
        ];
        $peerUsers = $users->reject(fn($u) => in_array($u->email, $primaryDemoEmails, true))->values();

        // Simulasi jumlah peserta realistis untuk gelombang Bimtek di katalog
        $catalogPeerTargets = [
            'AKPK-B1-2026'    => 36, // kuota 50
            'RTKD-2026-I'     => 35, // kuota 40 (>= 85%)
            'SMK3-A3-2026'    => 42, // kuota 60
            'SIPKERJA-2026'   => 18, // kuota 45
            'HKM-HI-B2'       => 27, // kuota null (tanpa batas kuota)
            'PPMI-LTSA-2026'  => 31, // kuota 35 (>= 85%)
            'PROD-NAS-2026-I' => 38, // ongoing
            'BIGDATA-2026-I'  => 44, // ongoing
            'IKK-ERGO-2026-I' => 29, // ongoing
        ];

        foreach ($courseData as $item) {
            $course = Course::where('slug', Str::slug($item['name']))->with(['sections.contents'])->first();
            if (!$course) {
                continue;
            }

            $allContents = $course->sections->flatMap->contents;
            $allContentIds = $allContents->pluck('id')->toArray();
            $allPostTests = PostTest::with('questions')->where('course_id', $course->id)->get();
            $allPostTestIds = $allPostTests->pluck('id')->toArray();

            $activeBimtek = BimtekEvent::where('course_id', $course->id)->latest()->first();
            $bimtekEventId = $activeBimtek?->id;

            if ($item['is_enrolled']) {
                // =============================================================
                // ENROLL KE KURSUS SAYA
                // =============================================================
                $targetPct = $item['target_pct'] ?? 0;

                foreach ($users as $studentIdx => $student) {
                    // Reset status lama di pivot & progress konten untuk kursus ini
                    if (!empty($allContentIds)) {
                        StudentContentProgress::where('user_id', $student->id)
                            ->whereIn('section_content_id', $allContentIds)
                            ->delete();
                    }
                    if (!empty($allPostTestIds)) {
                        DB::table('post_test_results')
                            ->where('user_id', $student->id)
                            ->whereIn('post_test_id', $allPostTestIds)
                            ->delete();
                    }

                    $zoomAttended = 0;
                    $completedAt = null;

                    if ($targetPct >= 100) {
                        // Selesai / Lulus 100%
                        $status = 'completed';
                        $completedAt = now()->subDay();

                        // Selesaikan semua materi
                        foreach ($allContents as $content) {
                            StudentContentProgress::create([
                                'user_id'            => $student->id,
                                'section_content_id' => $content->id,
                                'completed_at'       => now()->subDays(2),
                            ]);
                        }

                        // Luluskan semua post test modul & evaluasi akhir
                        foreach ($allPostTests as $pt) {
                            $row = [
                                'id'           => (string) Str::uuid(),
                                'user_id'      => $student->id,
                                'post_test_id' => $pt->id,
                                'score'        => 90,
                                'is_passed'    => 1,
                                'status'       => 'graded',
                                'completed_at' => now()->subDay(),
                                'created_at'   => now()->subDay(),
                                'updated_at'   => now()->subDay(),
                            ];

                            if ($pt->is_essay) {
                                $essayResponses = $pt->questions->values()->map(function ($q, $qIdx) use ($course) {
                                    return $qIdx === 0
                                        ? "Pada implementasi {$course->name}, instansi kami memulai dari pemetaan regulasi, pengumpulan data primer lintas unit kerja, serta validasi indikator lapangan secara berkala."
                                        : "Berdasarkan studi kasus tersebut, kendala utama di lapangan adalah sinkronisasi data antar bagian. Mitigasi yang diterapkan mencakup standardisasi SOP pelaporan dan audit kepatuhan berkala.";
                                })->all();
                                $row['answers'] = json_encode($essayResponses);
                                $row['notes'] = 'Analisis uraian sudah tajam, sistematis, dan selaras dengan standar studi kasus.';
                            } elseif ($pt->is_file_upload) {
                                $row['file_path'] = 'assignments/demo/Contoh_Jawaban_Tugas_Peserta.pdf';
                                $row['file_name'] = 'Laporan_Tugas_' . Str::slug(Str::limit($course->name, 25, ''), '_') . '.pdf';
                                $row['score'] = 92;
                                $row['notes'] = 'Berkas tugas tersusun lengkap sesuai sistematika lembar kerja.';
                            }

                            DB::table('post_test_results')->insert($row);
                        }

                        $zoomAttended = $activeBimtek ? 1 : 0;
                    } elseif ($targetPct > 0) {
                        // Sedang Berjalan (In Progress)
                        $status = 'in_progress';
                        // Selesaikan sebagian materi (misal 2 materi)
                        $countToComplete = max(1, (int) round(($targetPct / 100) * $allContents->count()));
                        foreach ($allContents->take($countToComplete) as $content) {
                            StudentContentProgress::create([
                                'user_id'            => $student->id,
                                'section_content_id' => $content->id,
                                'completed_at'       => now()->subHours(5),
                            ]);
                        }

                        // Jika bimtek online makro, sesi zoom kemarin sudah dihadiri & sebagian peserta sudah submit uraian/upload berkas
                        if ($item['bimtek'] && $item['bimtek']['batch_code'] === 'PTK-MAKRO-2026-I') {
                            $zoomAttended = 1;

                            if ($studentIdx < 6) {
                                $isPendingReview = $studentIdx < 4;
                                $sampleEvaluationTests = $allPostTests
                                    ->whereNotNull('course_section_id')
                                    ->take(2)
                                    ->concat($allPostTests->whereNull('course_section_id')->take(1));

                                foreach ($sampleEvaluationTests as $pt) {
                                    if ($pt->is_essay) {
                                        $essayResponses = $pt->questions->values()->map(function ($q, $qIdx) {
                                            return $qIdx === 0
                                                ? 'Langkah strategis pertama adalah menyelaraskan proyeksi kebutuhan tenaga kerja makro dengan dokumen RPJMD daerah menggunakan analisis rasio elastisitas sektoral.'
                                                : 'Mitigasi risiko ketidaksesuaian pasokan dan kebutuhan kerja dilakukan melalui koordinasi berkala antara Disnaker, Bappeda, dan balai pelatihan kerja setempat.';
                                        })->all();

                                        DB::table('post_test_results')->insert([
                                            'id'           => (string) Str::uuid(),
                                            'user_id'      => $student->id,
                                            'post_test_id' => $pt->id,
                                            'answers'      => json_encode($essayResponses),
                                            'score'        => $isPendingReview ? 85 : 88,
                                            'is_passed'    => 1,
                                            'status'       => $isPendingReview ? 'submitted' : 'graded',
                                            'notes'        => $isPendingReview ? null : 'Penjelasan metodologi sudah tepat dan mudah dipahami.',
                                            'completed_at' => now()->subHours(2 + $studentIdx),
                                            'created_at'   => now()->subHours(2 + $studentIdx),
                                            'updated_at'   => now()->subHours(2 + $studentIdx),
                                        ]);
                                    } elseif ($pt->is_file_upload) {
                                        $filePrefix = $pt->course_section_id ? 'Tugas_Perencanaan_Makro_' : 'Evaluasi_Akhir_Perencanaan_Makro_';
                                        DB::table('post_test_results')->insert([
                                            'id'           => (string) Str::uuid(),
                                            'user_id'      => $student->id,
                                            'post_test_id' => $pt->id,
                                            'file_path'    => 'assignments/demo/Contoh_Jawaban_Tugas_Peserta.pdf',
                                            'file_name'    => $filePrefix . ($studentIdx + 1) . '.pdf',
                                            'score'        => $isPendingReview ? 100 : 90,
                                            'is_passed'    => 1,
                                            'status'       => $isPendingReview ? 'submitted' : 'graded',
                                            'notes'        => $isPendingReview ? 'Mohon arahan terkait lampiran proyeksi sektor industri pada halaman 3.' : 'Lampiran proyeksi sektor industri sudah akurat.',
                                            'completed_at' => now()->subHours(1 + $studentIdx),
                                            'created_at'   => now()->subHours(1 + $studentIdx),
                                            'updated_at'   => now()->subHours(1 + $studentIdx),
                                        ]);
                                    }
                                }
                            }
                        }
                    } else {
                        // Belum Mulai (0%)
                        $status = 'enrolled';
                    }

                    // Sinkronisasi pivot course_student
                    DB::table('course_student')->updateOrInsert(
                        [
                            'course_id' => $course->id,
                            'user_id'   => $student->id,
                        ],
                        [
                            'bimtek_event_id' => $bimtekEventId,
                            'status'          => $status,
                            'progress'        => $targetPct,
                            'zoom_attended'   => $zoomAttended,
                            'completed_at'    => $completedAt,
                            'created_at'      => now()->subDays(5),
                            'updated_at'      => now(),
                        ]
                    );
                }
            } else {
                // =============================================================
                // KATALOG (PASTIKAN AKUN DEMO BELUM TERDAFTAR, ISI SIMULASI PESERTA LAIN)
                // =============================================================
                DB::table('course_student')
                    ->where('course_id', $course->id)
                    ->delete();

                if (!empty($allContentIds)) {
                    StudentContentProgress::whereIn('section_content_id', $allContentIds)->delete();
                }

                if (!empty($allPostTestIds)) {
                    DB::table('post_test_results')->whereIn('post_test_id', $allPostTestIds)->delete();
                }

                if ($bimtekEventId && $peerUsers->isNotEmpty()) {
                    $batchCode = $item['bimtek']['batch_code'] ?? '';
                    $targetPeers = $catalogPeerTargets[$batchCode] ?? 0;
                    $isOngoingEvent = ($item['bimtek']['status'] ?? '') === 'ongoing';

                    if ($targetPeers > 0) {
                        $selectedPeers = $peerUsers->shuffle()->take(min($targetPeers, $peerUsers->count()));
                        $nowTs = now();
                        $pivotRows = $selectedPeers->map(fn($peer) => [
                            'course_id'              => $course->id,
                            'user_id'                => $peer->id,
                            'bimtek_event_id'        => $bimtekEventId,
                            'status'                 => $isOngoingEvent ? 'in_progress' : 'enrolled',
                            'progress'               => $isOngoingEvent ? 15 : 0,
                            'zoom_attended'          => 0,
                            'cert_submission_status' => 'belum_diajukan',
                            'created_at'             => $nowTs->copy()->subDays(rand(1, 3)),
                            'updated_at'             => $nowTs,
                        ])->all();

                        if (!empty($pivotRows)) {
                            DB::table('course_student')->insert($pivotRows);
                        }
                    }
                }
            }
        }

        $this->command->info("CourseSeeder berhasil mengonfigurasi 4 Kursus Saya dan 12 Kursus Katalog! 🚀");
    }
}
