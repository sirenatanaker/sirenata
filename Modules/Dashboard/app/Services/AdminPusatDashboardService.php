<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use Creasi\Nusa\Models\Province;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\CertificateSubmission;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestResult;
use Modules\Project\Models\Project;
use Modules\RTK\Enums\RTKStatusVerification;
use Modules\RTK\Enums\StatusDocument;
use Modules\RTK\Enums\TypeRtk;
use Modules\RTK\Models\RencanaTenagaKerja;
use Modules\User\Models\UserProfile;
use Spatie\Activitylog\Models\Activity;

class AdminPusatDashboardService
{
    /**
     * Get aggregated dashboard data for Admin Pusat.
     */
    public function getDashboardData(
        User $user,
        ?int $selectedEventYear = null,
        string|int $selectedRtkYear = 'all',
        string|int $selectedRtkEndYear = 'all'
    ): array {
        $currentYear = (int) date('Y');
        $selectedEventYear = $currentYear;
        $lmsEventYears = [$currentYear];

        // 1. Stats Admin & Activity
        $totalAdminPusat = User::role('admin-pusat')->count();
        $adminAktif = User::role(['admin-province', 'admin-kab-kota'])->count();
        $aktivitasAdmin = Activity::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // 2. Gender distribution
        $genderMale = UserProfile::where('gender', 'male')->count();
        $genderFemale = UserProfile::where('gender', 'female')->count();

        // 3. Pantauan Operasional LMS by Event
        $lmsEventOverview = $this->getLmsEventOverview($selectedEventYear);

        // 5. Masa Aktif RTK per Provinsi
        $availableRtkYears = RencanaTenagaKerja::where('type', TypeRtk::PROVINSI->value)
            ->berlaku()
            ->pluck('start_date')
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        $rtkMasaAktifPerProvinsi = $this->getRtkMasaAktifPerProvinsi($selectedRtkYear);

        // 6. Masa Berakhir RTK per Provinsi
        $availableRtkEndYears = RencanaTenagaKerja::where('type', TypeRtk::PROVINSI->value)
            ->berlaku()
            ->pluck('end_date')
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        $rtkMasaBerakhirPerProvinsi = $this->getRtkMasaBerakhirPerProvinsi($selectedRtkEndYear);

        // 7. Status Distribusi RTK
        $rtkStatusDistribution = DB::table('rencana_tenaga_kerjas')
            ->select('status_verification as status', DB::raw('count(*) as total'))
            ->where(function ($query) {
                $query->where('status_verification', '!=', RTKStatusVerification::PENDING->value)
                    ->orWhere(function ($q) {
                        $q->where('status_verification', RTKStatusVerification::PENDING->value)
                            ->where('is_active', true)
                            ->whereIn('type', [
                                TypeRtk::PROVINSI->value,
                                TypeRtk::KAB_KOTA->value,
                            ]);
                    });
            })
            ->groupBy('status_verification')
            ->pluck('total', 'status');

        // 8. Year Filter Options
        $maxOptionYear = !empty($availableRtkEndYears) ? max($availableRtkEndYears) : $currentYear;
        $minOptionYear = $currentYear;
        $rtkYearsOptions = [];
        for ($y = $maxOptionYear; $y >= $minOptionYear; $y--) {
            $rtkYearsOptions[] = $y;
        }

        $minAvailableEndYear = !empty($availableRtkEndYears) ? min($availableRtkEndYears) : $currentYear;
        $maxAvailableEndYear = !empty($availableRtkEndYears) ? max($availableRtkEndYears) : $currentYear;
        $rtkEndYearsOptions = [];
        for ($y = $maxAvailableEndYear; $y >= $minAvailableEndYear; $y--) {
            $rtkEndYearsOptions[] = $y;
        }

        // 9. Pending Approvals
        $allPendingApprovals = $this->getAllPendingApprovals();
        $initialPendingApprovals = $allPendingApprovals->slice(0, 10)->values();
        $hasMorePendingApprovals = $allPendingApprovals->count() > 10;

        return [
            'user' => $user,
            'totalAdminPusat' => $totalAdminPusat,
            'adminAktif' => $adminAktif,
            'aktivitasAdmin' => $aktivitasAdmin,
            'genderMale' => $genderMale,
            'genderFemale' => $genderFemale,
            'lmsEventOverview' => $lmsEventOverview,
            'lmsEventYears' => $lmsEventYears,
            'selectedEventYear' => $selectedEventYear,
            'rtkMasaAktifPerProvinsi' => $rtkMasaAktifPerProvinsi,
            'rtkMasaBerakhirPerProvinsi' => $rtkMasaBerakhirPerProvinsi,
            'rtkStatusDistribution' => $rtkStatusDistribution,
            'selectedRtkYear' => $selectedRtkYear,
            'selectedRtkEndYear' => $selectedRtkEndYear,
            'rtkYearsOptions' => $rtkYearsOptions,
            'rtkEndYearsOptions' => $rtkEndYearsOptions,
            'initialPendingApprovals' => $initialPendingApprovals,
            'hasMorePendingApprovals' => $hasMorePendingApprovals,
            'totalPendingApprovals' => $allPendingApprovals->count(),
        ];
    }

    /**
     * Daftar tahun pelaksanaan Bimtek Event (selalu menyertakan tahun berjalan).
     */
    public function getLmsEventYears(): array
    {
        $currentYear = (int) date('Y');

        $years = BimtekEvent::query()
            ->selectRaw('YEAR(COALESCE(start_date, created_at)) as year')
            ->distinct()
            ->pluck('year')
            ->filter()
            ->map(fn($y) => (int) $y)
            ->push($currentYear)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return $years;
    }

    /**
     * Ringkasan operasional LMS by Event untuk Admin Pusat pada tahun terpilih.
     */
    public function getLmsEventOverview(int $year): array
    {
        $events = BimtekEvent::with('course')
            ->where(function ($q) use ($year) {
                $q->whereYear('start_date', $year)
                  ->orWhere(function ($q2) use ($year) {
                      $q2->whereNull('start_date')->whereYear('created_at', $year);
                  });
            })
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();

        if ($events->isEmpty()) {
            return [
                'year' => $year,
                'summary' => [
                    'total_events'              => 0,
                    'ongoing_events'            => 0,
                    'open_registration_events'  => 0,
                    'completed_events'          => 0,
                    'total_participants'        => 0,
                    'total_completed'           => 0,
                    'pending_evaluations'       => 0,
                    'ready_for_ppsdm'           => 0,
                    'pending_ppsdm_submissions' => 0,
                    'total_certified'           => 0,
                ],
                'chart'             => [],
                'events'            => [],
                'ppsdm_submissions' => [],
            ];
        }

        $eventIds = $events->pluck('id')->all();
        $courseIds = $events->pluck('course_id')->filter()->unique()->values()->all();

        // Batch 1: seluruh enrollment peserta pada kursus terkait
        $enrollments = !empty($courseIds)
            ? DB::table('course_student')
                ->whereIn('course_id', $courseIds)
                ->select('course_id', 'bimtek_event_id', 'user_id', 'status', 'cert_submission_status', 'official_cert_number', 'certificate_file')
                ->get()
                ->groupBy('course_id')
            : collect();

        // Batch 2: evaluasi non-CBT (essay & file_upload) dan jawaban yang masih butuh pemeriksaan/publikasi
        $manualPostTests = !empty($courseIds)
            ? PostTest::whereIn('course_id', $courseIds)
                ->whereIn('type', ['essay', 'file_upload'])
                ->select('id', 'course_id')
                ->get()
                ->groupBy('course_id')
            : collect();

        $allManualPtIds = $manualPostTests->flatten()->pluck('id')->all();
        $pendingResultsByPt = !empty($allManualPtIds)
            ? PostTestResult::whereIn('post_test_id', $allManualPtIds)
                ->whereIn('grading_status', ['PENDING', 'QUEUED', 'PROCESSING', 'FAILED', 'MANUAL_REVIEW', 'DRAFT_GRADED'])
                ->select('post_test_id', 'user_id')
                ->get()
                ->groupBy('post_test_id')
            : collect();

        // Batch 3: pengajuan PPSDM aktif (menunggu nomor atau penerbitan PDF)
        $activeSubmissions = CertificateSubmission::with('bimtekEvent')
            ->whereIn('bimtek_event_id', $eventIds)
            ->whereIn('status', ['submitted_to_ppsdm', 'numbered'])
            ->latest('submitted_at')
            ->get();

        $summary = [
            'total_events'              => $events->count(),
            'ongoing_events'            => 0,
            'open_registration_events'  => 0,
            'completed_events'          => 0,
            'total_participants'        => 0,
            'total_completed'           => 0,
            'pending_evaluations'       => 0,
            'ready_for_ppsdm'           => 0,
            'pending_ppsdm_submissions' => $activeSubmissions->count(),
            'total_certified'           => 0,
        ];

        $eventItems = [];

        foreach ($events as $ev) {
            $courseEnrolls = $enrollments->get($ev->course_id, collect())
                ->filter(fn($row) => $row->bimtek_event_id === $ev->id || $row->bimtek_event_id === null);

            $enrolledUserIds = $courseEnrolls->pluck('user_id')->unique()->all();
            $registered = count($enrolledUserIds);
            $completed = $courseEnrolls->where('status', 'completed')->count();
            $certified = $courseEnrolls->filter(fn($r) => !empty($r->official_cert_number) || !empty($r->certificate_file))->count();
            $readyPpsdm = $courseEnrolls->filter(fn($r) =>
                $r->status === 'completed'
                && empty($r->official_cert_number)
                && $r->cert_submission_status !== 'diajukan_ke_ppsdm'
            )->count();

            $coursePts = $manualPostTests->get($ev->course_id, collect());
            $pendingEval = 0;
            if ($coursePts->isNotEmpty() && !empty($enrolledUserIds)) {
                $userLookup = array_flip($enrolledUserIds);
                foreach ($coursePts as $pt) {
                    $ptPending = $pendingResultsByPt->get($pt->id, collect());
                    foreach ($ptPending as $res) {
                        if (isset($userLookup[$res->user_id])) {
                            $pendingEval++;
                        }
                    }
                }
            }

            $badge = $ev->status_badge;
            $statusLabel = (string) ($badge['label'] ?? 'Draft');
            $statusKey = match ($statusLabel) {
                'Sedang Berlangsung' => 'ongoing',
                'Pendaftaran Buka'   => 'open_registration',
                'Selesai'            => 'completed',
                default              => 'other',
            };

            if ($statusKey === 'ongoing') {
                $summary['ongoing_events']++;
            } elseif ($statusKey === 'open_registration') {
                $summary['open_registration_events']++;
            } elseif ($statusKey === 'completed') {
                $summary['completed_events']++;
            }

            $summary['total_participants'] += $registered;
            $summary['total_completed'] += $completed;
            $summary['pending_evaluations'] += $pendingEval;
            $summary['ready_for_ppsdm'] += $readyPpsdm;
            $summary['total_certified'] += $certified;

            $regStart = $ev->registration_start_at ?? $ev->start_date?->copy()->subDays(7);
            $regEnd   = $ev->registration_end_at ?? $ev->start_date?->copy()->subDay();
            $execStart = $ev->start_date;
            $execEnd   = $ev->end_date ?? $ev->start_date;
            $evalStart = $ev->assessment_start_at ?? $execEnd;
            $evalEnd   = $ev->assessment_end_at ?? $execEnd?->copy()->addDays(3);

            // Pastikan setiap fase memiliki rentang valid (end >= start)
            if ($regStart && $regEnd && $regEnd->lt($regStart)) {
                $regEnd = $regStart->copy();
            }
            if ($execStart && $execEnd && $execEnd->lt($execStart)) {
                $execEnd = $execStart->copy();
            }
            if ($evalStart && $evalEnd && $evalEnd->lt($evalStart)) {
                $evalEnd = $evalStart->copy();
            }

            $fmtRange = function ($s, $e): string {
                if (!$s) return '-';
                if (!$e || $s->isSameDay($e)) return $s->translatedFormat('d M Y');
                if ($s->month === $e->month && $s->year === $e->year) {
                    return $s->format('d') . ' – ' . $e->translatedFormat('d M Y');
                }
                return $s->translatedFormat('d M') . ' – ' . $e->translatedFormat('d M Y');
            };

            $schedule = $fmtRange($execStart, $execEnd);

            $eventItems[] = [
                'id'           => $ev->id,
                'batch_name'   => $ev->batch_name,
                'course_name'  => $ev->course?->name ?? '-',
                'schedule'     => $schedule,
                'status_key'   => $statusKey,
                'status_label' => $statusLabel,
                'status_class' => $badge['class'] ?? 'bg-slate-100 text-slate-700',
                'quota'        => $ev->quota ? (int) $ev->quota : null,
                'registered'   => $registered,
                'completed'    => $completed,
                'certified'    => $certified,
                'pending_eval' => $pendingEval,
                'ready_ppsdm'  => $readyPpsdm,
                'reg_start'    => $regStart?->format('Y-m-d'),
                'reg_end'      => $regEnd?->format('Y-m-d'),
                'reg_label'    => $fmtRange($regStart, $regEnd),
                'exec_start'   => $execStart?->format('Y-m-d'),
                'exec_end'     => $execEnd?->format('Y-m-d'),
                'exec_label'   => $fmtRange($execStart, $execEnd),
                'eval_start'   => $evalStart?->format('Y-m-d'),
                'eval_end'     => $evalEnd?->format('Y-m-d'),
                'eval_label'   => $fmtRange($evalStart, $evalEnd),
                'sort_date'    => $execStart?->format('Y-m-d') ?? $regStart?->format('Y-m-d') ?? '9999-12-31',
                'url'          => route('admin-pusat.management-course.bimtek-events.show', $ev->id),
                'eval_url'     => route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $ev->id, 'tab' => 'evaluation']),
                'ppsdm_url'    => route('admin-pusat.management-course.bimtek-events.show', ['bimtek_event' => $ev->id, 'tab' => 'graduation']),
            ];
        }

        // Urutkan kronologis berdasarkan tanggal mulai pelaksanaan agar Gantt Chart membentuk tangga rapi
        $ganttEvents = collect($eventItems)
            ->sortBy('sort_date')
            ->values()
            ->all();

        $ppsdmList = $activeSubmissions->map(function ($sub) {
            $b = $sub->status_badge;
            return [
                'id'              => $sub->id,
                'submission_code' => $sub->submission_code,
                'batch_name'      => $sub->bimtekEvent?->batch_name ?? '-',
                'total_graduates' => (int) $sub->total_graduates,
                'status_label'    => $b['label'] ?? 'Diproses',
                'status_class'    => $b['class'] ?? 'bg-amber-50 text-amber-700 border-amber-200',
                'url'             => route('admin-pusat.management-course.certificate-submissions.show', $sub->id),
            ];
        })->values()->all();

        return [
            'year'              => $year,
            'today'             => now()->format('Y-m-d'),
            'today_label'       => now()->translatedFormat('d M Y'),
            'summary'           => $summary,
            'events'            => $ganttEvents,
            'ppsdm_submissions' => $ppsdmList,
        ];
    }

    /**
     * Get SDM per Provinsi.
     */
    public function getSdmPerProvinsi(int $selectedSdmYear): Collection
    {
        $userCountsByProvince = DB::table('user_scopes')
            ->join('users', 'user_scopes.user_id', '=', 'users.id')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_uuid')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.uuid')
            ->where('roles.name', 'user')
            ->whereYear('users.created_at', $selectedSdmYear)
            ->whereNotNull('user_scopes.province_code')
            ->select('user_scopes.province_code', DB::raw('count(*) as total'))
            ->groupBy('user_scopes.province_code')
            ->get();

        $provinceCodes = $userCountsByProvince->pluck('province_code')->unique()->toArray();
        $provinces = Province::whereIn('code', $provinceCodes)->pluck('name', 'code');

        return $userCountsByProvince->map(function ($row) use ($provinces) {
            $code = $row->province_code;
            return (object) [
                'province_code' => $code,
                'province_name' => collect(explode(' ', $provinces[$code] ?? 'Unknown (' . $code . ')'))
                    ->map(fn($w) => ucfirst(strtolower($w)))
                    ->join(' '),
                'total' => (int) $row->total,
            ];
        })->sortByDesc('total')->values();
    }

    /**
     * Get monthly SDM registration trend (Jan - Dec) for a year.
     */
    public function getSdmMonthlyTrend(int $selectedSdmYear): array
    {
        $monthlyCounts = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_uuid')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.uuid')
            ->where('roles.name', 'user')
            ->whereYear('users.created_at', $selectedSdmYear)
            ->selectRaw('MONTH(users.created_at) as month, count(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = (int) ($monthlyCounts[$m] ?? 0);
        }
        return $months;
    }

    /**
     * Get RTK Masa Aktif per Provinsi.
     */
    public function getRtkMasaAktifPerProvinsi(string|int $selectedRtkYear): Collection
    {
        $queryRtk = RencanaTenagaKerja::where('type', TypeRtk::PROVINSI->value)->berlaku();

        if ($selectedRtkYear !== 'all') {
            $queryRtk->where('end_date', '>=', (int) $selectedRtkYear);
        }

        $rtkProvinsi = $queryRtk->get();
        $rtkProvinceCodes = $rtkProvinsi->pluck('province_code')->toArray();
        $rtkProvinceNames = Province::whereIn('code', $rtkProvinceCodes)->pluck('name', 'code');

        return $rtkProvinsi->groupBy('province_code')->map(function ($items, $code) use ($rtkProvinceNames) {
            $rtk = $items->first();
            return (object) [
                'province_name' => $rtkProvinceNames[$code] ?? 'Unknown',
                'sisa_tahun' => max(0, (int) $rtk->end_date - (int) date('Y')),
                'start_date' => (int) $rtk->start_date,
                'end_date' => (int) $rtk->end_date,
                'total' => $items->count(),
            ];
        })->sortBy('province_name')->values();
    }

    /**
     * Get RTK Masa Berakhir per Provinsi.
     */
    public function getRtkMasaBerakhirPerProvinsi(string|int $selectedRtkEndYear): Collection
    {
        $queryRtkEnd = RencanaTenagaKerja::where('type', TypeRtk::PROVINSI->value)->berlaku();

        if ($selectedRtkEndYear !== 'all') {
            $queryRtkEnd->where('end_date', (int) $selectedRtkEndYear);
        }

        $rtkProvinsiEnd = $queryRtkEnd->get();
        $rtkEndProvinceCodes = $rtkProvinsiEnd->pluck('province_code')->toArray();
        $rtkEndProvinceNames = Province::whereIn('code', $rtkEndProvinceCodes)->pluck('name', 'code');

        return $rtkProvinsiEnd->groupBy('province_code')->map(function ($items, $code) use ($rtkEndProvinceNames) {
            $rtk = $items->first();
            return (object) [
                'province_name' => $rtkEndProvinceNames[$code] ?? 'Unknown',
                'sisa_tahun' => max(0, (int) $rtk->end_date - (int) date('Y')),
                'start_date' => (int) $rtk->start_date,
                'end_date' => (int) $rtk->end_date,
                'total' => $items->count(),
            ];
        })->sortBy('province_name')->values();
    }

    /**
     * Get all pending approvals (Projects + RTK).
     */
    public function getAllPendingApprovals(): Collection
    {
        $pendingProjects = Project::whereIn(DB::raw('LOWER(status)'), ['draft'])
            ->get()
            ->map(function ($item) {
                $regionName = 'Daerah';
                $rtkData = \Modules\Project\Models\ProjectRtkData::where('project_id', $item->id)->first();

                if ($rtkData && !empty($rtkData->nama_daerah)) {
                    $regionName = $rtkData->nama_daerah;
                } else {
                    $userId = $item->created_by ?? $item->team_leader;
                    if ($userId) {
                        $scope = DB::table('user_scopes')->where('user_id', $userId)->first();
                        if ($scope) {
                            if (!empty($scope->regency_code)) {
                                $regionName = \Modules\MasterData\Models\Regency::where('code', $scope->regency_code)->value('name') ?? $regionName;
                            } elseif (!empty($scope->province_code)) {
                                $regionName = \Modules\MasterData\Models\Province::where('code', $scope->province_code)->value('name') ?? $regionName;
                            }
                        }
                    }
                }

                return [
                    'id' => $item->id,
                    'category' => 'Projek (' . ucfirst($item->type ?? 'Daerah') . ')',
                    'title' => $item->name,
                    'subtitle' => $regionName,
                    'created_at' => $item->created_at?->toISOString(),
                    'date_formatted' => $item->created_at ? $item->created_at->diffForHumans() : '-',
                    'type' => 'project',
                    'badge_color' => 'bg-[#547996] text-white border-transparent',
                    'url' => route('admin-pusat.project.show', $item->id),
                ];
            });

        $pendingRtk = RencanaTenagaKerja::with(['province'])
            ->where('is_active', true)
            ->where('type', TypeRtk::PROVINSI->value)
            ->where(function ($query) {
                $query->where('status_verification', RTKStatusVerification::PENDING->value)
                    ->orWhere(function ($q) {
                        $q->where('status_verification', RTKStatusVerification::APPROVED->value)
                            ->where('status_document', '!=', StatusDocument::VALID->value);
                    });
            })
            ->latest()
            ->get()
            ->unique('province_code')
            ->map(function ($item) {
                $provinceCode = $item->province_code ?? $item->province?->code;
                $statusVerifVal = is_object($item->status_verification) ? $item->status_verification->value : $item->status_verification;
                $statusDocVal = is_object($item->status_document) ? $item->status_document->value : $item->status_document;

                $isVerifApproved = ($statusVerifVal === RTKStatusVerification::APPROVED->value || $statusVerifVal === 'approved');
                $isDocValid = ($statusDocVal === StatusDocument::VALID->value || $statusDocVal === 'valid');

                return [
                    'id' => $item->id,
                    'category' => 'RTK Provinsi',
                    'title' => $item->name,
                    'subtitle' => $item->province?->name ?? 'Provinsi',
                    'created_at' => $item->created_at?->toISOString(),
                    'date_formatted' => $item->created_at ? $item->created_at->diffForHumans() : '-',
                    'type' => 'rtk',
                    'url' => $provinceCode
                        ? route('admin-pusat.rtkd.show-province', $provinceCode)
                        : route('admin-pusat.rtkd.index'),
                    'verification_label' => $isVerifApproved ? 'Sudah diverifikasi' : 'Menunggu diverifikasi',
                    'verification_color' => $isVerifApproved
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-amber-50 text-amber-700 border border-amber-200',
                    'document_label' => $isDocValid ? 'Valid' : 'Menunggu persetujuan',
                    'document_color' => $isDocValid
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-amber-50 text-amber-700 border border-amber-200',
                    'action_label' => $isVerifApproved ? 'Tinjau Dokumen' : 'Tinjau Verifikasi',
                ];
            });

        return $pendingRtk
            ->concat($pendingProjects)
            ->sortByDesc('created_at')
            ->values();
    }

    /**
     * Get paginated pending approvals for AJAX infinite scroll.
     */
    public function getPendingApprovalsPage(int $page, int $perPage = 10): array
    {
        $all = $this->getAllPendingApprovals();
        $pagedItems = $all->slice(($page - 1) * $perPage, $perPage)->values();
        $hasMore = $all->count() > ($page * $perPage);

        return [
            'data' => $pagedItems,
            'has_more' => $hasMore,
            'page' => $page,
            'total' => $all->count(),
        ];
    }
}
