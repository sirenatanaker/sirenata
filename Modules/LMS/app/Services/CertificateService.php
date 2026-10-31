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
        $backgroundUrl = $activeSetting->background_image ? Storage::url($activeSetting->background_image) : null;
        $signatureUrl = $activeSetting->signature_image ? Storage::url($activeSetting->signature_image) : null;
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
            'verification_url' => route('verify-certificate', $certificateCode),
        ]);

        $pdf->setPaper('a5', 'landscape');
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
