<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\CertificateSetting;
use Modules\LMS\Models\CertificateSubmission;
use Modules\LMS\Models\Course;
use Modules\LMS\Services\CertificateService;

class CertificateSubmissionController extends Controller
{
    /**
     * Display a listing of Certificate Submissions to PPSDM.
     */
    public function index(Request $request)
    {
        $query = CertificateSubmission::with(['bimtekEvent.course', 'submitter'])->latest();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('submission_code', 'like', "%{$search}%")
                  ->orWhere('submission_letter_number', 'like', "%{$search}%")
                  ->orWhere('ppsdm_decree_number', 'like', "%{$search}%")
                  ->orWhereHas('bimtekEvent', function ($b) use ($search) {
                      $b->where('batch_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $submissions = $query->paginate(10)->withQueryString();

        // Metrik Ringkasan
        $totalSubmissions = CertificateSubmission::count();
        $pendingSubmissions = CertificateSubmission::where('status', 'submitted_to_ppsdm')->count();
        $completedSubmissions = CertificateSubmission::where('status', 'completed')->count();
        $totalCertifiedASN = DB::table('course_student')->whereNotNull('official_cert_number')->count();

        return view('lms::admin-pusat.certificate-submissions.index', compact(
            'submissions',
            'totalSubmissions',
            'pendingSubmissions',
            'completedSubmissions',
            'totalCertifiedASN'
        ));
    }

    /**
     * Show form to create a new Certificate Submission.
     */
    public function create(Request $request)
    {
        $events = BimtekEvent::with('course')->latest()->get();
        $selectedEventId = $request->input('bimtek_event_id');
        $selectedEvent = null;
        $eligibleStudents = collect();

        if ($selectedEventId) {
            $selectedEvent = BimtekEvent::with('course')->find($selectedEventId);
            if ($selectedEvent) {
                // Cari peserta kursus yang tuntas (progress 100% dan status completed)
                $eligibleStudents = DB::table('course_student')
                    ->join('users', 'users.id', '=', 'course_student.user_id')
                    ->where('course_student.course_id', $selectedEvent->course_id)
                    ->where('course_student.status', 'completed')
                    ->where(function ($q) {
                        $q->whereNull('course_student.cert_submission_status')
                          ->orWhere('course_student.cert_submission_status', 'belum_diajukan');
                    })
                    ->select(
                        'users.id as user_id',
                        'users.name as user_name',
                        'users.email as user_email',
                        'course_student.progress',
                        'course_student.completed_at',
                        'course_student.zoom_attended'
                    )
                    ->orderBy('users.name')
                    ->get();
            }
        }

        return view('lms::admin-pusat.certificate-submissions.create', compact(
            'events',
            'selectedEventId',
            'selectedEvent',
            'eligibleStudents'
        ));
    }

    /**
     * Store a newly created Certificate Submission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bimtek_event_id'          => 'required|exists:bimtek_events,id',
            'submission_letter_number' => 'nullable|string|max:150',
            'notes'                    => 'nullable|string',
            'student_ids'              => 'required|array|min:1',
            'student_ids.*'            => 'exists:users,id',
        ]);

        $event = BimtekEvent::findOrFail($validated['bimtek_event_id']);
        $submissionCode = 'SUB-PPSDM-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $submission = CertificateSubmission::create([
            'bimtek_event_id'          => $event->id,
            'submission_code'          => $submissionCode,
            'submission_letter_number' => $validated['submission_letter_number'] ?? null,
            'total_graduates'          => count($validated['student_ids']),
            'submitted_by'             => auth()->id(),
            'submitted_at'             => now(),
            'status'                   => 'submitted_to_ppsdm',
            'notes'                    => $validated['notes'] ?? null,
        ]);

        // Hubungkan peserta ke pengajuan ini
        DB::table('course_student')
            ->where('course_id', $event->course_id)
            ->whereIn('user_id', $validated['student_ids'])
            ->update([
                'bimtek_event_id'        => $event->id,
                'cert_submission_id'     => $submission->id,
                'cert_submission_status' => 'diajukan_ke_ppsdm',
            ]);

        return redirect()->route('admin-pusat.management-course.certificate-submissions.show', $submission->id)
            ->with('success', "Berkas pengajuan sertifikat #{$submissionCode} berhasil diajukan ke PPSDM!");
    }

    /**
     * Display the specified Certificate Submission.
     */
    public function show($id)
    {
        $submission = CertificateSubmission::with(['bimtekEvent.course', 'submitter'])->findOrFail($id);

        $students = DB::table('course_student')
            ->join('users', 'users.id', '=', 'course_student.user_id')
            ->where('course_student.cert_submission_id', $submission->id)
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                'course_student.progress',
                'course_student.status',
                'course_student.completed_at',
                'course_student.zoom_attended',
                'course_student.official_cert_number',
                'course_student.certificate_code',
                'course_student.certificate_file'
            )
            ->orderBy('users.name')
            ->get();

        return view('lms::admin-pusat.certificate-submissions.show', compact('submission', 'students'));
    }

    /**
     * Ekspor Berkas Rekapitulasi Calon Lulusan format CSV / Excel untuk Lampiran Nota Dinas PPSDM.
     */
    public function exportExcel($id)
    {
        $submission = CertificateSubmission::with(['bimtekEvent.course'])->findOrFail($id);

        $students = DB::table('course_student')
            ->join('users', 'users.id', '=', 'course_student.user_id')
            ->where('course_student.cert_submission_id', $submission->id)
            ->select(
                'users.name as user_name',
                'users.email as user_email',
                'course_student.progress',
                'course_student.completed_at',
                'course_student.zoom_attended',
                'course_student.official_cert_number'
            )
            ->orderBy('users.name')
            ->get();

        $filename = "Rekap_Pengajuan_Sertifikat_PPSDM_{$submission->submission_code}.csv";
        $handle = fopen('php://temp', 'w+');

        // BOM UTF-8 agar karakter bahasa Indonesia terbaca rapi di Microsoft Excel
        fputs($handle, "\xEF\xBB\xBF");

        // Header CSV
        fputcsv($handle, [
            'NO',
            'NAMA PESERTA',
            'EMAIL / IDENTITAS',
            'NAMA BIMTEK',
            'GELOMBANG / BATCH',
            'PROGRESS MATERI (%)',
            'KEHADIRAN ZOOM',
            'TANGGAL LULUS',
            'NOMOR SERTIFIKAT PPSDM (DIISI PPSDM)'
        ]);

        foreach ($students as $idx => $st) {
            fputcsv($handle, [
                $idx + 1,
                $st->user_name,
                $st->user_email,
                $submission->bimtekEvent->course->name,
                $submission->bimtekEvent->batch_name,
                $st->progress . '%',
                $st->zoom_attended ? 'Hadir' : 'Tidak Hadir',
                $st->completed_at ? Carbon::parse($st->completed_at)->format('d/m/Y') : '-',
                $st->official_cert_number ?? ''
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Input / Simpan Nomor Registrasi Sertifikat dari PPSDM (Bisa Pola Berurutan atau Per Peserta).
     */
    public function saveNumbers(Request $request, $id)
    {
        $submission = CertificateSubmission::findOrFail($id);

        $request->validate([
            'mode'                 => 'required|in:pattern,manual',
            'ppsdm_decree_number'  => 'nullable|string|max:150',
            'number_pattern'       => 'nullable|string', // e.g., "800.1.2/PPSDM/2026/{NUMBER}"
            'start_number'         => 'nullable|integer|min:1',
            'student_numbers'      => 'nullable|array',
        ]);

        $students = DB::table('course_student')
            ->where('cert_submission_id', $submission->id)
            ->orderBy('user_id')
            ->get();

        if ($request->input('mode') === 'pattern') {
            $pattern = $request->input('number_pattern', '800.1.2/PPSDM/' . date('Y') . '/{NUMBER}');
            $startNum = (int) $request->input('start_number', 1);

            foreach ($students as $idx => $st) {
                $curNumber = str_pad((string) ($startNum + $idx), 4, '0', STR_PAD_LEFT);
                $finalNumber = str_replace('{NUMBER}', $curNumber, $pattern);

                DB::table('course_student')
                    ->where('course_id', $st->course_id)
                    ->where('user_id', $st->user_id)
                    ->update([
                        'official_cert_number' => $finalNumber,
                    ]);
            }
        } else {
            $numbers = $request->input('student_numbers', []);
            foreach ($numbers as $userId => $num) {
                if (!empty($num)) {
                    DB::table('course_student')
                        ->where('course_id', $submission->bimtekEvent->course_id)
                        ->where('user_id', $userId)
                        ->update([
                            'official_cert_number' => trim($num),
                        ]);
                }
            }
        }

        $submission->update([
            'ppsdm_decree_number' => $request->input('ppsdm_decree_number') ?: $submission->ppsdm_decree_number,
            'numbered_at'         => now(),
            'status'              => 'numbered',
        ]);

        return back()->with('success', 'Nomor registrasi resmi sertifikat dari PPSDM berhasil disimpan!');
    }

    /**
     * Terbitkan Dokumen PDF e-Sertifikat Resmi untuk Semua Peserta yang Memiliki Nomor PPSDM.
     */
    public function generatePdfs(Request $request, $id, CertificateService $certService)
    {
        $submission = CertificateSubmission::with(['bimtekEvent.course'])->findOrFail($id);

        $students = DB::table('course_student')
            ->where('cert_submission_id', $submission->id)
            ->whereNotNull('official_cert_number')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Belum ada peserta yang memiliki nomor sertifikat PPSDM.');
        }

        $activeSetting = CertificateSetting::getActive();
        if (!$activeSetting) {
            return back()->with('error', 'Pengaturan template dan tanda tangan sertifikat belum diatur pada menu Manajemen Sertifikat.');
        }

        $generatedCount = 0;

        foreach ($students as $st) {
            $user = User::find($st->user_id);
            if (!$user) continue;

            $course = $submission->bimtekEvent->course;
            $certificateCode = $st->certificate_code ?: ('CERT-PPSDM-' . date('Y') . '-' . strtoupper(Str::random(6)));
            $issuedAt = now();
            $backgroundUrl = $activeSetting->background_image ? Storage::url($activeSetting->background_image) : null;
            $signatureUrl = $activeSetting->signature_image ? Storage::url($activeSetting->signature_image) : null;
            $completedDate = Carbon::parse($st->completed_at ?: $issuedAt)->translatedFormat('d F Y');
            $fullName = $user->profile?->full_name ?? $user->name;

            // Generate PDF menggunakan view sertifikat
            $pdf = Pdf::loadView('lms::admin-pusat.certificates.certificate-template', [
                'nama_peserta'         => $fullName,
                'nama_kursus'          => $course->name,
                'tanggal_selesai'      => $completedDate,
                'nomor_sertifikat'     => $st->official_cert_number, // Gunakan nomor resmi PPSDM!
                'certificate_code'     => $certificateCode,
                'background_url'       => $backgroundUrl,
                'signature_url'        => $signatureUrl,
                'signer_name'          => $activeSetting->signer_name,
                'signer_title'         => $activeSetting->signer_title,
                'verification_url'     => route('verify-certificate', $certificateCode),
            ]);

            $pdf->setPaper('a5', 'landscape');
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

            $pdfFileName = 'certificates/pdfs/cert-' . $course->slug . '-' . $user->id . '.pdf';

            if (!Storage::disk('public')->exists('certificates/pdfs')) {
                Storage::disk('public')->makeDirectory('certificates/pdfs');
            }

            Storage::disk('public')->put($pdfFileName, $pdf->output());

            DB::table('course_student')
                ->where('course_id', $course->id)
                ->where('user_id', $user->id)
                ->update([
                    'certificate_code'       => $certificateCode,
                    'certificate_file'       => $pdfFileName,
                    'certificate_issued_at'  => $issuedAt,
                    'cert_submission_status' => 'terbit',
                ]);

            $generatedCount++;
        }

        $submission->update(['status' => 'completed']);

        return back()->with('success', "Alhamdulillah! Sebanyak {$generatedCount} e-Sertifikat resmi berhasil diterbitkan dengan nomor PPSDM!");
    }
}
