<?php

namespace Modules\LMS\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\LMS\Models\CertificateSetting;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\BimtekInstructorEvaluation;

class CertificateService
{
    public function paginateSettings(?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return CertificateSetting::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('signer_name', 'like', '%' . $search . '%')
                        ->orWhere('signer_title', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                $q->where('is_active', $status === 'active');
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createSetting(array $data): CertificateSetting
    {
        $setting = new CertificateSetting();
        $setting->signer_name = $data['signer_name'];
        $setting->signer_title = $data['signer_title'];
        $setting->is_active = false;

        if (!empty($data['signature_image'])) {
            $setting->signature_image = $data['signature_image']->store('certificates/signatures', 'public');
        }

        if (!empty($data['background_image'])) {
            $setting->background_image = $data['background_image']->store('certificates/templates', 'public');
        }

        $setting->save();

        return $setting;
    }

    public function updateSetting(CertificateSetting $certificate, array $data): CertificateSetting
    {
        $certificate->signer_name = $data['signer_name'];
        $certificate->signer_title = $data['signer_title'];

        if (!empty($data['signature_image'])) {
            if ($certificate->signature_image) {
                Storage::disk('public')->delete($certificate->signature_image);
            }
            $certificate->signature_image = $data['signature_image']->store('certificates/signatures', 'public');
        }

        if (!empty($data['background_image'])) {
            if ($certificate->background_image) {
                Storage::disk('public')->delete($certificate->background_image);
            }
            $certificate->background_image = $data['background_image']->store('certificates/templates', 'public');
        }

        $certificate->save();

        return $certificate;
    }

    public function activateSetting(CertificateSetting $certificate): CertificateSetting
    {
        return DB::transaction(function () use ($certificate) {
            CertificateSetting::where('is_active', true)->update(['is_active' => false]);
            $certificate->is_active = true;
            $certificate->save();

            return $certificate;
        });
    }

    public function deleteSetting(CertificateSetting $certificate): void
    {
        if ($certificate->signature_image) {
            Storage::disk('public')->delete($certificate->signature_image);
        }

        if ($certificate->background_image) {
            Storage::disk('public')->delete($certificate->background_image);
        }

        $certificate->delete();
    }

    /**
     * Konversi file gambar di disk public menjadi base64 data URI agar 100% terbaca oleh DomPDF
     * tanpa bergantung pada URL HTTP / path relatif di Windows maupun Linux.
     */
    public function resolveImageDataUri(?string $relativePath): ?string
    {
        if (empty($relativePath) || !Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($relativePath);
        $contents = @file_get_contents($fullPath);
        if ($contents === false) {
            return null;
        }

        $mime = @mime_content_type($fullPath) ?: 'image/png';

        // DomPDF memiliki dukungan terbatas untuk WebP; konversi otomatis ke PNG bila GD tersedia.
        if ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $img = @imagecreatefromwebp($fullPath);
            if ($img !== false) {
                ob_start();
                imagepng($img);
                $pngData = ob_get_clean();
                imagedestroy($img);
                if ($pngData !== false) {
                    return 'data:image/png;base64,' . base64_encode($pngData);
                }
            }
        }

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    public function generateStudentCertificate(User $user, string $slug): array
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $enrollment = $course->students()->wherePivot('user_id', $user->id)->first();

        if (!$enrollment) {
            return ['success' => false, 'message' => 'Anda belum terdaftar di kursus ini.'];
        }

        if ($enrollment->pivot->status !== 'completed' || $enrollment->pivot->progress < 100) {
            return ['success' => false, 'message' => 'Anda belum menyelesaikan kursus ini.'];
        }

        if (!empty($enrollment->pivot->bimtek_event_id)) {
            $hasEvaluated = BimtekInstructorEvaluation::where('bimtek_event_id', $enrollment->pivot->bimtek_event_id)
                ->where('user_id', $user->id)
                ->exists();
            if (!$hasEvaluated) {
                return ['success' => false, 'message' => 'Anda harus mengisi evaluasi narasumber & fasilitas bimtek terlebih dahulu sebelum menerbitkan sertifikat.'];
            }
        }

        // Wajib melengkapi informasi pribadi & instansi di profil sebelum klaim sertifikat
        $user->loadMissing(['profile', 'scopeArea']);
        if (!$user->hasCompleteCertificateProfile()) {
            $missing = $user->missingCertificateProfileFields();

            return [
                'success'          => false,
                'requires_profile' => true,
                'missing_fields'   => $missing,
                'message'          => 'Lengkapi informasi pribadi di halaman Profil terlebih dahulu sebelum mengklaim sertifikat (' . implode(', ', $missing) . ').',
            ];
        }

        $activeSetting = CertificateSetting::getActive();
        if (!$activeSetting) {
            return ['success' => false, 'message' => 'Pengaturan sertifikat aktif belum diatur oleh admin.'];
        }

        $certificateCode = $enrollment->pivot->certificate_code;
        if (!empty($certificateCode)) {
            return [
                'success' => false,
                'message' => 'Sertifikat sudah diterbitkan dan tidak dapat dibuat ulang. Unduh dokumen yang sudah ada.',
            ];
        }

        do {
            $certificateCode = 'CERT-' . date('Y') . '-' . strtoupper(Str::random(6));
            $codeExists = DB::table('course_student')
                ->where('certificate_code', $certificateCode)
                ->exists();
        } while ($codeExists);

        $issuedAt = now();
        $backgroundUrl = $this->resolveImageDataUri($activeSetting->background_image);
        $signatureUrl = $this->resolveImageDataUri($activeSetting->signature_image);
        $completedDate = Carbon::parse($issuedAt)->translatedFormat('d F Y');
        $fullName = $user->siapkerja_id ? $user->name : ($user->profile?->full_name ?? $user->name);

        $displayCertNumber = $enrollment->pivot->official_cert_number ?: $certificateCode;

        $pdf = Pdf::loadView('lms::admin-pusat.certificates.certificate-template', [
            'nama_peserta'     => $fullName,
            'nama_kursus'      => $course->name,
            'tanggal_selesai'  => $completedDate,
            'nomor_sertifikat' => $displayCertNumber,
            'certificate_code' => $certificateCode,
            'background_url'   => $backgroundUrl,
            'signature_url'    => $signatureUrl,
            'signer_name'      => $activeSetting->signer_name,
            'signer_title'     => $activeSetting->signer_title,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

        $pdfFileName = 'certificates/pdfs/cert-' . $course->slug . '-' . $user->id . '.pdf';

        if (!Storage::disk('public')->exists('certificates/pdfs')) {
            Storage::disk('public')->makeDirectory('certificates/pdfs');
        }

        Storage::disk('public')->put($pdfFileName, $pdf->output());

        $course->students()->updateExistingPivot($user->id, [
            'certificate_code'      => $certificateCode,
            'certificate_file'      => $pdfFileName,
            'certificate_issued_at' => $issuedAt,
        ]);

        return [
            'success' => true,
            'message' => 'Sertifikat Anda berhasil disiapkan dengan data terbaru!',
        ];
    }
}
