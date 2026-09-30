<?php

namespace Modules\Project\Http\Controllers\AdminKabKota;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Models\Project;
use Modules\Project\Enums\ProjectType;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    protected string $routePrefix = 'admin-kab-kota.project.';
    protected string $projectScope = 'daerah';

    public function index(Request $request)
    {
        $query = Project::with('leader')->latest();
        $query->where('type', ProjectType::KAB_KOTA->value);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->paginate($request->get('per_page', 10))->withQueryString();
        $routePrefix = $this->routePrefix;
        $projectScope = $this->projectScope;

        return view('project::index', compact('projects', 'routePrefix', 'projectScope'));
    }

    public function create()
    {
        $routePrefix = $this->routePrefix;
        $projectScope = $this->projectScope;

        return view('project::create', compact('routePrefix', 'projectScope'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proyekName'  => 'required|string|max:255',
            'startDate'   => 'required|date',
            'endDate'     => 'required|date|after_or_equal:startDate',
            'duration'    => 'nullable|integer',
            'sk_document' => 'required|file|mimes:pdf|max:5120',
        ]);

        $skPath = null;
        if ($request->hasFile('sk_document')) {
            $skPath = $request->file('sk_document')->store('project_sk', 'public');
        }

        Project::create([
            'name'       => $request->proyekName,
            'start_date' => $request->startDate,
            'end_date'   => $request->endDate,
            'duration'   => $request->duration,
            'sk_document'=> $skPath,
            'created_by' => Auth::id(),
            'type'       => ProjectType::KAB_KOTA->value,
            'status'     => 'Draft', // Otomatis Draft, menunggu persetujuan Pusat
        ]);

        ToastMagic::success('Draft proyek berhasil dibuat! Menunggu persetujuan Pusat.');
        
        return redirect()->route($this->routePrefix . 'index', ['type' => $this->projectScope]);
    }

    public function show($id)
    {
        $project = Project::with(['leader'])->findOrFail($id);
        $routePrefix = $this->routePrefix;
        $projectScope = $this->projectScope;

        return view('project::show', compact('project', 'routePrefix', 'projectScope'));
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $routePrefix = $this->routePrefix;
        $projectScope = $this->projectScope;

        // Cegah pengeditan jika proyek sudah kedaluwarsa
        if ($project->status === 'Kedaluwarsa') {
            ToastMagic::error('Proyek telah kedaluwarsa karena melebihi batas waktu penentuan tim.');
            return redirect()->route($this->routePrefix . 'index', ['type' => $this->projectScope]);
        }

        $users = collect();

        // Ambil data user yang memenuhi kriteria HANYA jika status 'Menunggu Tim' atau 'On Progress'
        if (in_array($project->status, ['Menunggu Tim', 'On Progress'])) {
            $user = Auth::user();
            $adminScope = $user->scopeArea;

            $usersQuery = User::role('user');

            // Filter 1: Area Wilayah (Kabupaten/Kota)
            if ($adminScope && $adminScope->regency_code) {
                $usersQuery->whereHas('scopeArea', function ($q) use ($adminScope) {
                    $q->where('regency_code', $adminScope->regency_code);
                });
            } else {
                $usersQuery->where('id', 0); // Fallback aman
            }

            // Filter 2: Prasyarat Kursus (Bypass relasi model, tembak langsung ke tabel pivot)
            $prerequisiteCourseIds = $project->prerequisiteCourseIds();
            if ($project->is_prerequisite_active && !empty($prerequisiteCourseIds)) {
                $usersQuery->whereIn('id', function ($query) use ($prerequisiteCourseIds) {
                    $query->select('user_id')
                        ->from('course_student')
                        ->whereIn('course_id', $prerequisiteCourseIds)
                        ->where('progress', '>=', 100)
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(DISTINCT course_id) = ?', [count($prerequisiteCourseIds)]);
                });
            }

            $users = $usersQuery->get();
        }

        return view('project::edit', compact('project', 'users', 'routePrefix', 'projectScope'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        if ($project->status === 'Kedaluwarsa') {
            ToastMagic::error('Gagal memperbarui: Proyek telah kedaluwarsa.');
            return redirect()->route($this->routePrefix . 'index', ['type' => $this->projectScope]);
        }

        $rules = [
            'proyekName'  => 'required|string|max:255',
            'startDate'   => 'required|date',
            'endDate'     => 'required|date|after_or_equal:startDate',
            'duration'    => 'nullable|integer',
            'sk_document' => 'nullable|file|mimes:pdf|max:5120',
        ];

        // Jalankan validasi Tim jika status 'Menunggu Tim' atau 'On Progress'
        $isAssigningTeam = in_array($project->status, ['Menunggu Tim', 'On Progress']);

        if ($isAssigningTeam) {
            $adminScope = Auth::user()->scopeArea;

            $usersQuery = User::role('user')->whereHas('scopeArea', function ($q) use ($adminScope) {
                $q->where('regency_code', $adminScope?->regency_code);
            });

            // Filter Prasyarat Kursus jika aktif
            $prerequisiteCourseIds = $project->prerequisiteCourseIds();
            if ($project->is_prerequisite_active && !empty($prerequisiteCourseIds)) {
                $usersQuery->whereIn('id', function ($query) use ($prerequisiteCourseIds) {
                    $query->select('user_id')
                        ->from('course_student')
                        ->whereIn('course_id', $prerequisiteCourseIds)
                        ->where('progress', '>=', 100)
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(DISTINCT course_id) = ?', [count($prerequisiteCourseIds)]);
                });
            }

            $allowedUserIds = $usersQuery->pluck('id')->toArray();

            $rules['teamLeader'] = [
                'required',
                'exists:users,id',
                Rule::in($allowedUserIds)
            ];
            $rules['teamMembers']   = 'nullable|array';
            $rules['teamMembers.*'] = [
                'exists:users,id',
                Rule::in($allowedUserIds)
            ];
        }

        $request->validate($rules);

        // Data dasar yang diperbarui
        $updateData = [
            'name'       => $request->proyekName,
            'start_date' => $request->startDate,
            'end_date'   => $request->endDate,
            'duration'   => $request->duration,
        ];

        // Update dokumen SK jika ada file baru
        if ($request->hasFile('sk_document')) {
            if ($project->sk_document) {
                Storage::disk('public')->delete($project->sk_document);
            }
            $updateData['sk_document'] = $request->file('sk_document')->store('project_sk', 'public');
        }

        // Update Tim dan ubah status jika sedang pada tahapan penentuan tim
        if ($isAssigningTeam) {
            $updateData['team_leader']  = $request->teamLeader;
            $updateData['team_members'] = $request->teamMembers ?? [];

            // Jika status sebelumnya 'Menunggu Tim', ubah otomatis ke 'On Progress'
            if ($project->status === 'Menunggu Tim') {
                $updateData['status'] = 'On Progress';
            }
        }

        $project->update($updateData);

        ToastMagic::success('Proyek berhasil diperbarui!');
        
        return redirect()->route($this->routePrefix . 'index', ['type' => $this->projectScope]);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        if ($project->sk_document) {
            Storage::disk('public')->delete($project->sk_document);
        }

        $project->delete();
        ToastMagic::success('Proyek berhasil dihapus!');
        
        return redirect()->route($this->routePrefix . 'index', ['type' => $this->projectScope]);
    }
}