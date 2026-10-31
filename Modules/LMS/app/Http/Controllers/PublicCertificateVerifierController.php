<?php

namespace Modules\LMS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\LMS\Models\Course;

class PublicCertificateVerifierController extends Controller
{
    /**
     * Public page to verify authenticity of a certificate.
     */
    public function verify($code)
    {
        $record = DB::table('course_student')
            ->where('certificate_code', $code)
            ->orWhere('official_cert_number', $code)
            ->first();

        if (!$record) {
            return view('lms::public.verify-certificate', [
                'isValid' => false,
                'code'    => $code,
            ]);
        }

        $user = User::find($record->user_id);
        $course = Course::find($record->course_id);
        $bimtek = $record->bimtek_event_id ? \Modules\LMS\Models\BimtekEvent::find($record->bimtek_event_id) : null;

        return view('lms::public.verify-certificate', [
            'isValid'              => true,
            'code'                 => $record->certificate_code,
            'officialCertNumber'   => $record->official_cert_number,
            'studentName'          => $user?->profile?->full_name ?? $user?->name ?? 'Peserta ASN',
            'studentEmail'         => $user?->email,
            'courseName'           => $course?->name ?? 'Kursus Pembelajaran',
            'batchName'            => $bimtek?->batch_name ?? 'Pelatihan Online',
            'issuedAt'             => $record->certificate_issued_at ? \Carbon\Carbon::parse($record->certificate_issued_at)->translatedFormat('d F Y') : '-',
            'completedAt'          => $record->completed_at ? \Carbon\Carbon::parse($record->completed_at)->translatedFormat('d F Y') : '-',
        ]);
    }
}
