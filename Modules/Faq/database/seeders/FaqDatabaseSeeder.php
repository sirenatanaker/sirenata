<?php

namespace Modules\Faq\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Faq\Models\Faq;
use Modules\Roles\Models\Role;
use App\Models\User;

class FaqDatabaseSeeder extends Seeder
{
    public function run()
    {
        $admin = User::whereHas('roles', function ($q) {
            $q->where('name', 'admin-pusat');
        })->first();

        if (!$admin) {
            $this->command->error('No Admin Pusat user found. Please seed users first.');
            return;
        }

        $this->command->info('Seeding structured FAQ data...');

        // [pertanyaan, jawaban, target roles]
        $faqs = [
            ['Bagaimana cara login ke sistem?', 'Gunakan email dan password yang telah didaftarkan oleh Admin.', ['admin-province', 'admin-kab-kota']],
            ['Bagaimana cara reset password?', 'Klik "Lupa Password" di halaman login, lalu ikuti instruksi yang dikirim ke email Anda.', ['admin-province', 'admin-kab-kota']],
            ['Apa itu E-Learning SIRENATA?', 'SIRENATA adalah platform e-learning untuk pelatihan kerja di seluruh Indonesia.', ['admin-province', 'admin-kab-kota']],
            ['Bagaimana cara mendaftarkan instansi baru di provinsi saya?', 'Silakan hubungi admin provinsi masing-class melalui menu Bantuan.', ['admin-province']],
            ['Kapan jadwal rekapitulasi data tingkat provinsi?', 'Rekapitulasi data tingkat provinsi dilakukan setiap akhir bulan.', ['admin-province']],
            ['Apakah ada pelatihan khusus Admin Provinsi?', 'Ya, pelatihan khusus Admin Provinsi diadakan setiap kuartal oleh pusat.', ['admin-province']],
            ['Dimana saya bisa melihat daftar peserta di daerah saya?', 'Anda bisa melihatnya di menu Peserta pada Dashboard Kab/Kota.', ['admin-kab-kota']],
            ['Bagaimana cara reset wilayah penugasan peserta?', 'Fitur ini hanya bisa dilakukan oleh Admin Provinsi atau Pusat.', ['admin-kab-kota']],
            ['Kapan pendaftaran pelatihan tingkat Kabupaten/Kota dibuka?', 'Pendaftaran dibuka secara serentak setiap awal bulan melaui website resmi.', ['admin-kab-kota']],
            ['Bagaimana cara masuk ke aplikasi?', 'Masuk menggunakan akun SIAPKerja Anda melalui tombol Masuk di halaman utama.', ['user']],
            ['Bagaimana cara mengikuti kursus?', 'Buka menu Katalog, pilih kursus yang tersedia, lalu tekan tombol Daftar.', ['user']],
            ['Bagaimana cara mendapatkan sertifikat?', 'Selesaikan seluruh materi dan lulus evaluasi akhir, sertifikat otomatis tersedia di halaman Profil pada tab Sertifikat Saya.', ['user']],
            ['Saya lupa password, bagaimana cara reset?', 'Karena login memakai SSO SIAPKerja, reset password dilakukan melalui akun SIAPKerja Anda.', ['user']],
        ];

        $roleIds = Role::whereIn('name', Faq::targetableRoles())->pluck('uuid', 'name');

        foreach ($faqs as [$question, $answer, $roles]) {
            $faq = Faq::updateOrCreate(
                ['question' => $question],
                [
                    'answer' => $answer,
                    'created_by' => $admin->id,
                ]
            );
            $faq->roles()->sync($roleIds->only($roles)->values()->all());
        }

        $this->command->info('Seeded FAQ data with role targets (idempotent).');
    }
}
