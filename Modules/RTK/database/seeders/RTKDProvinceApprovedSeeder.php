<?php

namespace Modules\RTK\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\MasterData\Models\Province;
use Modules\RTK\Enums\RTKStatusVerification;
use Modules\RTK\Enums\StatusDocument;
use Modules\RTK\Enums\TypeRtk;
use Modules\RTK\Models\RencanaTenagaKerja;

class RTKDProvinceApprovedSeeder extends Seeder
{
    /**
     * Seed 10 dokumen RTK Provinsi Acuan yang sudah diverifikasi & disetujui (Berlaku)
     * dengan variasi masa berlaku (Masih berlaku, Segera berakhir, dan Sudah berakhir)
     * agar grafik Komparasi Masa Berlaku RTK di Dashboard Admin Pusat terisi representatif.
     */
    public function run(): void
    {
        $approver = User::role('admin-pusat')->first()
            ?? User::role('super-admin')->first()
            ?? User::first();

        if (!$approver) {
            $this->command?->warn('User admin-pusat tidak ditemukan. Lewati RTKDProvinceApprovedSeeder.');
            return;
        }

        // 10 Provinsi acuan dengan variasi masa berlaku (tahun sekarang: 2026)
        $targets = [
            ['code' => '31', 'start' => 2025, 'end' => 2029, 'days_ago' => 14], // DKI Jakarta — Masih berlaku
            ['code' => '32', 'start' => 2025, 'end' => 2030, 'days_ago' => 12], // Jawa Barat — Masih berlaku
            ['code' => '33', 'start' => 2024, 'end' => 2028, 'days_ago' => 20], // Jawa Tengah — Masih berlaku
            ['code' => '34', 'start' => 2026, 'end' => 2030, 'days_ago' => 5],  // DI Yogyakarta — Masih berlaku
            ['code' => '35', 'start' => 2024, 'end' => 2029, 'days_ago' => 18], // Jawa Timur — Masih berlaku
            ['code' => '64', 'start' => 2025, 'end' => 2029, 'days_ago' => 9],  // Kalimantan Timur — Masih berlaku
            ['code' => '36', 'start' => 2023, 'end' => 2027, 'days_ago' => 30], // Banten — Segera berakhir (<= 1 thn)
            ['code' => '51', 'start' => 2022, 'end' => 2026, 'days_ago' => 45], // Bali — Segera berakhir (<= 1 thn)
            ['code' => '12', 'start' => 2023, 'end' => 2027, 'days_ago' => 25], // Sumatera Utara — Segera berakhir (<= 1 thn)
            ['code' => '73', 'start' => 2021, 'end' => 2025, 'days_ago' => 90], // Sulawesi Selatan — Sudah berakhir
        ];

        $provinces = Province::whereIn('code', collect($targets)->pluck('code'))->get()->keyBy('code');

        DB::transaction(function () use ($targets, $provinces, $approver) {
            $seeded = 0;

            foreach ($targets as $item) {
                $province = $provinces->get($item['code']);
                if (!$province) {
                    $this->command?->warn("Provinsi kode {$item['code']} tidak ditemukan, dilewati.");
                    continue;
                }

                $owner = User::role('admin-province')
                    ->whereHas('scopeArea', fn($q) => $q->where('province_code', $province->code))
                    ->first() ?? $approver;

                $docName = "RTKD Provinsi {$province->name} {$item['start']}-{$item['end']}";

                // Nonaktifkan RTK Provinsi aktif lain milik provinsi ini agar hanya ada 1 RTK Acuan aktif
                RencanaTenagaKerja::where('type', TypeRtk::PROVINSI->value)
                    ->where('province_code', $province->code)
                    ->where('name', '!=', $docName)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                RencanaTenagaKerja::updateOrCreate(
                    [
                        'type'          => TypeRtk::PROVINSI->value,
                        'province_code' => $province->code,
                        'name'          => $docName,
                    ],
                    [
                        'user_id'             => $owner->id,
                        'regency_code'        => null,
                        'start_date'          => $item['start'],
                        'end_date'            => $item['end'],
                        'status_verification' => RTKStatusVerification::APPROVED->value,
                        'status_document'     => StatusDocument::VALID->value,
                        'is_active'           => true,
                        'approved_by'         => $approver->id,
                        'approved_at'         => now()->subDays($item['days_ago']),
                        'rejected_reason'     => null,
                    ]
                );

                $seeded++;
            }

            $this->command?->info("Berhasil menyemai {$seeded} RTK Provinsi Acuan (Disetujui & Berlaku).");
        });
    }
}
