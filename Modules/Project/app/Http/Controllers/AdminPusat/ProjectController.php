<?php

namespace Modules\Project\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use App\Models\User;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Project\Enums\ProjectType;
use Modules\Project\Exports\ProjectExport;
use Modules\Project\Models\Project;

class ProjectController extends Controller
{
    protected string $routePrefix = 'admin-pusat.project.';

    public function export(Request $request)
    {
        $filename = 'Daftar Proyek' . '-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(
            new ProjectExport(
                search: $request->string('search')->toString() ?: null,
                status: $request->string('status')->toString() ?: null,
                type: $request->string('type')->toString() ?: 'pusat',
            ),
            $filename
        );
    }

    public function index(Request $request)
    {
        $query = Project::with(['leader.profile', 'creator.scopeArea.province', 'creator.scopeArea.regency'])->latest();

        $projectScope = $request->get('type', $request->status === 'Draft' ? 'daerah' : 'pusat');

        if ($projectScope === 'daerah') {
            $query->whereIn('type', [ProjectType::PROVINSI->value, ProjectType::KAB_KOTA->value]);
        } else {
            $projectScope = 'pusat';
            $query->where('type', ProjectType::NASIONAL->value);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $projects = $query->paginate($request->get('per_page', 10))->withQueryString();
        $routePrefix = $this->routePrefix;

        return view('project::index', compact('projects', 'routePrefix', 'projectScope'));
    }

    public function create()
    {
        // 1. Ambil daftar kursus dari LMS
        $courseModelClass = match (true) {
            class_exists('Modules\LMS\Models\Course') => 'Modules\LMS\Models\Course',
            class_exists('Modules\Course\Models\Course') => 'Modules\Course\Models\Course',
            class_exists('App\Models\Course') => 'App\Models\Course',
            default => null,
        };

        $courses = $courseModelClass ? $courseModelClass::select('id', 'name')->orderBy('name')->get() : collect();

        // 2. Deteksi struktur kolom tabel LMS secara dinamis
        $hasDirectCourseId = \Illuminate\Support\Facades\Schema::hasColumn('section_contents', 'course_id');
        $fkSectionColumn = \Illuminate\Support\Facades\Schema::hasColumn('section_contents', 'course_section_id') ? 'course_section_id' : 'section_id';
        $sectionTable = \Illuminate\Support\Facades\Schema::hasTable('course_sections') ? 'course_sections' : 'sections';

        // 3. Hitung total jumlah materi/konten per course
        if ($hasDirectCourseId) {
            $totalContentsPerCourse = DB::table('section_contents')
                ->select('course_id', DB::raw('COUNT(id) as total_count'))
                ->groupBy('course_id')
                ->pluck('total_count', 'course_id')
                ->toArray();
        } else {
            $totalContentsPerCourse = DB::table('section_contents as sc')
                ->join("{$sectionTable} as s", "s.id", "=", "sc.{$fkSectionColumn}")
                ->select('s.course_id', DB::raw('COUNT(sc.id) as total_count'))
                ->groupBy('s.course_id')
                ->pluck('total_count', 's.course_id')
                ->toArray();
        }

        // 4. Ambil user pusat dan tentukan kursus yang lulus 100%
        $users = User::role('user')
            ->where(function ($query) {
                $query->doesntHave('scopeArea')
                    ->orWhereHas('scopeArea', function ($q) {
                        $q->whereNull('province_code')->whereNull('regency_code');
                    });
            })
            ->get()
            ->map(function ($user) use ($totalContentsPerCourse, $hasDirectCourseId, $sectionTable, $fkSectionColumn) {
                $query = DB::table('student_content_progress as scp')
                    ->join('section_contents as sc', 'sc.id', '=', 'scp.section_content_id')
                    ->where('scp.user_id', $user->id)
                    ->whereNotNull('scp.completed_at');

                if ($hasDirectCourseId) {
                    $userCompletedCounts = $query
                        ->select('sc.course_id', DB::raw('COUNT(DISTINCT scp.section_content_id) as completed_count'))
                        ->groupBy('sc.course_id')
                        ->pluck('completed_count', 'sc.course_id')
                        ->toArray();
                } else {
                    $userCompletedCounts = $query
                        ->join("{$sectionTable} as s", "s.id", "=", "sc.{$fkSectionColumn}")
                        ->select('s.course_id', DB::raw('COUNT(DISTINCT scp.section_content_id) as completed_count'))
                        ->groupBy('s.course_id')
                        ->pluck('completed_count', 's.course_id')
                        ->toArray();
                }

                $completedCourseIds = [];
                foreach ($userCompletedCounts as $courseId => $completedCount) {
                    $total = $totalContentsPerCourse[$courseId] ?? 0;
                    if ($total > 0 && $completedCount >= $total) {
                        $completedCourseIds[] = (int) $courseId;
                    }
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'completed_courses' => $completedCourseIds,
                ];
            });

        $routePrefix = $this->routePrefix;

        return view('project::create', compact('courses', 'users', 'routePrefix'));
    }

    public function store(Request $request)
    {
        $allowedUserIds = User::role('user')->where(function ($query) {
            $query->doesntHave('scopeArea')
                ->orWhereHas('scopeArea', function ($q) {
                    $q->whereNull('province_code')->whereNull('regency_code');
                });
        })->pluck('id')->toArray();

        $request->validate([
            'proyekName' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'duration' => 'nullable|integer',
            'prerequisite_course_ids' => 'nullable|array',
            'prerequisite_course_ids.*' => 'integer',
            'teamLeader' => [
                'required',
                'exists:users,id',
                \Illuminate\Validation\Rule::in($allowedUserIds)
            ],
            'teamMembers' => 'nullable|array',
            'teamMembers.*' => [
                'exists:users,id',
                \Illuminate\Validation\Rule::in($allowedUserIds)
            ],
            'sk_document' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $skPath = null;
        if ($request->hasFile('sk_document')) {
            $skPath = $request->file('sk_document')->store('projects/sk', 'public');
        }

        Project::create([
            'name' => $request->proyekName,
            'start_date' => $request->startDate,
            'end_date' => $request->endDate,
            'duration' => $request->duration,
            'created_by' => Auth::id(),
            'team_leader' => $request->teamLeader,
            'team_members' => $request->teamMembers,
            'prerequisite_course_ids' => $request->prerequisite_course_ids,
            'is_prerequisite_active' => !empty($request->prerequisite_course_ids),
            'sk_document' => $skPath,
            'type' => ProjectType::NASIONAL->value,
            'status' => 'On Progress', // Langsung Aktif / ACC
        ]);

        ToastMagic::success('Proyek berhasil dibuat dan langsung diaktifkan!');
        return redirect()->route($this->routePrefix . 'index');
    }

    public function show($id)
    {
        $project = Project::with(['leader.profile'])->findOrFail($id);
        $routePrefix = $this->routePrefix;
        return view('project::show', compact('project', 'routePrefix'));
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);

        // KUNCI ROLE: Jika proyek ini milik Provinsi atau Kab/Kota, 
        // langsung "lempar" Admin Pusat ke halaman khusus Prasyarat!
        if (in_array($project->type, [ProjectType::PROVINSI->value, ProjectType::KAB_KOTA->value])) {
            return redirect()->route($this->routePrefix . 'prerequisite', $project->id);
        }

        // Jika ini Proyek Nasional (milik Pusat sendiri), buka form edit normal
        $users = User::role('user')->where(function ($query) {
            $query->doesntHave('scopeArea')
                ->orWhereHas('scopeArea', function ($q) {
                    $q->whereNull('province_code')->whereNull('regency_code');
                });
        })->get();

        $routePrefix = $this->routePrefix;
        return view('project::edit', compact('project', 'users', 'routePrefix'));
    }



    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        ToastMagic::success('Proyek berhasil dihapus!');
        return redirect()->route($this->routePrefix . 'index');
    }

    /**
     * Menampilkan halaman persetujuan dan pengaturan prasyarat (Khusus Pusat)
     */
    public function prerequisite($id)
    {
        $project = Project::findOrFail($id);

        // Ambil semua daftar kursus dari LMS untuk dijadikan pilihan prasyarat
        $courses = \Modules\LMS\Models\Course::select('id', 'name')->orderBy('name')->get();

        $routePrefix = $this->routePrefix;

        return view('project::admin-pusat.prerequisite', compact('project', 'courses', 'routePrefix'));
    }

    /**
     * Menyimpan pengaturan prasyarat dan mengubah status proyek
     */
    public function updatePrerequisite(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'prerequisite_course_ids'   => 'nullable|array|required_if:is_prerequisite_active,1',
            'prerequisite_course_ids.*' => 'exists:courses,id',
            'status'                    => 'required|in:Draft,Menunggu Tim,On Progress,Completed,Kedaluwarsa',
        ]);

        $prerequisiteCourseIds = $request->input('prerequisite_course_ids', []);
        $previousStatus = $project->status;
        $newStatus = $request->status;

        $updateData = [
            'prerequisite_course_id'  => $prerequisiteCourseIds[0] ?? null,
            'prerequisite_course_ids' => $prerequisiteCourseIds ?: null,
            'is_prerequisite_active'  => $request->has('is_prerequisite_active'),
            'status'                  => $newStatus,
        ];

        // Jika berubah dari Draft -> Menunggu Tim ATAU dari Kedaluwarsa -> Menunggu Tim (Buka Kembali)
        if (($previousStatus === 'Draft' && $newStatus === 'Menunggu Tim') ||
            ($previousStatus === 'Kedaluwarsa' && $newStatus === 'Menunggu Tim')
        ) {
            $updateData['approved_at'] = now(); // Reset/Set timestamp persetujuan
        }

        $project->update($updateData);

        if ($previousStatus !== $project->status) {
            event(new \Modules\Project\Events\ProjectApprovalDecided(
                projectId: $project->id,
                projectName: $project->name,
                projectType: $project->type,
                creatorId: $project->created_by,
                previousStatus: $previousStatus,
                newStatus: (string) $project->status,
                actorId: auth()->id(),
            ));
        }

        ToastMagic::success('Status dan prasyarat proyek berhasil diperbarui!');
        return redirect()->route($this->routePrefix . 'index', ['type' => 'daerah']);
    }

    /**
     * Digunakan oleh Admin Daerah/Sistem untuk mengisi detail tim.
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Proteksi Real-time: Jika masa 14 hari sudah terlampaui saat menekan simpan
        if ($project->status === 'Menunggu Tim' && $project->approved_at) {
            if (now()->diffInDays($project->approved_at) >= 14) {
                $project->update(['status' => 'Kedaluwarsa']);

                ToastMagic::error('Masa tenggang penentuan tim (14 hari) telah berakhir. Status proyek berubah menjadi Kedaluwarsa.');
                return redirect()->back();
            }
        }

        $request->validate([
            'proyekName'  => 'required|string|max:255',
            'startDate'   => 'required|date',
            'endDate'     => 'required|date',
            'duration'    => 'nullable|integer',
            'teamLeader'  => 'required|exists:users,id',
            'teamMembers' => 'nullable|array',
        ]);

        $updateData = [
            'name'        => $request->proyekName,
            'start_date'  => $request->startDate,
            'end_date'    => $request->endDate,
            'duration'    => $request->duration,
            'team_leader' => $request->teamLeader,
            'team_members' => $request->teamMembers,
        ];

        // Transisi otomatis: Menunggu Tim -> On Progress (saat Ketua Tim terisi)
        if ($project->status === 'Menunggu Tim' && !empty($request->teamLeader)) {
            $updateData['status'] = 'On Progress';
        }

        $project->update($updateData);

        ToastMagic::success('Proyek berhasil diperbarui!');
        return redirect()->route($this->routePrefix . 'index');
    }
}
