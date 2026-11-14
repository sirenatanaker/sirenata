<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\BimtekEvaluationAnswer;
use Modules\LMS\Models\BimtekEvaluationQuestion;
use Modules\LMS\Models\BimtekInstructorEvaluation;

class BimtekEvaluationQuestionController extends Controller
{
    /**
     * Tampilkan daftar instrumen pertanyaan evaluasi dan rekapitulasi respon kuesioner.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'questions'); // 'questions' atau 'responses'
        $selectedEventId = $request->get('bimtek_event_id');

        // Daftar pertanyaan kuesioner
        $questions = BimtekEvaluationQuestion::orderBy('order')
            ->orderBy('created_at')
            ->get();

        // Data untuk Tab Rekapitulasi Respon
        $bimtekEvents = BimtekEvent::orderBy('batch_name')->get(['id', 'batch_name', 'batch_code']);

        $responsesQuery = BimtekInstructorEvaluation::with(['user', 'bimtekEvent', 'course', 'answers.question'])
            ->latest();

        if ($selectedEventId) {
            $responsesQuery->where('bimtek_event_id', $selectedEventId);
        }

        $responses = $responsesQuery->paginate(15)->withQueryString();

        // Hitung statistik per pertanyaan skala
        $scaleStats = [];
        $scaleQuestions = $questions->where('type', 'scale');

        foreach ($scaleQuestions as $sq) {
            $answersQuery = BimtekEvaluationAnswer::where('question_id', $sq->id)
                ->whereNotNull('rating');

            if ($selectedEventId) {
                $answersQuery->whereHas('evaluation', function ($q) use ($selectedEventId) {
                    $q->where('bimtek_event_id', $selectedEventId);
                });
            }

            $count = $answersQuery->count();
            $avg = $count > 0 ? round((float) $answersQuery->avg('rating'), 1) : 0.0;

            $scaleStats[$sq->id] = [
                'count' => $count,
                'avg'   => $avg,
            ];
        }

        $totalEvaluationsCount = $responsesQuery->count();
        $overallAvg = 0.0;
        if (!empty($scaleStats)) {
            $sumAvg = array_sum(array_column($scaleStats, 'avg'));
            $overallAvg = count($scaleStats) > 0 ? round($sumAvg / count($scaleStats), 1) : 0.0;
        }

        return view('lms::admin-pusat.evaluation-questions.index', compact(
            'tab',
            'questions',
            'bimtekEvents',
            'selectedEventId',
            'responses',
            'scaleStats',
            'totalEvaluationsCount',
            'overallAvg'
        ));
    }

    /**
     * Form tambah pertanyaan kuesioner baru.
     */
    public function create(): View
    {
        $sections = BimtekEvaluationQuestion::distinct()->pluck('section')->filter()->values();
        if ($sections->isEmpty()) {
            $sections = collect(['Materi & Pemateri', 'Fasilitas & Penyelenggaraan', 'Catatan & Masukan']);
        }

        $nextOrder = (BimtekEvaluationQuestion::max('order') ?? 0) + 1;

        return view('lms::admin-pusat.evaluation-questions.create', compact('sections', 'nextOrder'));
    }

    /**
     * Simpan instrumen pertanyaan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question'    => 'required|string|max:1000',
            'description' => 'nullable|string|max:1000',
            'type'        => 'required|in:scale,text',
            'min_label'   => 'nullable|string|max:100',
            'max_label'   => 'nullable|string|max:100',
            'section'     => 'required|string|max:150',
            'order'       => 'required|integer|min:1',
            'is_required' => 'boolean',
            'is_active'   => 'boolean',
        ], [
            'question.required' => 'Teks pertanyaan kuesioner wajib diisi.',
            'section.required'  => 'Kelompok/Bagian pertanyaan wajib ditentukan.',
            'order.required'    => 'Nomor urutan pertanyaan wajib diisi.',
        ]);

        $validated['is_required'] = $request->boolean('is_required', true);
        $validated['is_active']   = $request->boolean('is_active', true);
        $validated['scale_max']   = 5;

        if ($validated['type'] === 'text') {
            $validated['min_label'] = null;
            $validated['max_label'] = null;
        }

        BimtekEvaluationQuestion::create($validated);

        ToastMagic::success('Pertanyaan kuesioner evaluasi berhasil ditambahkan!');

        return redirect()->route('admin-pusat.management-course.evaluation-questions.index');
    }

    /**
     * Form edit pertanyaan kuesioner.
     */
    public function edit(string $id): View
    {
        $question = BimtekEvaluationQuestion::findOrFail($id);

        $sections = BimtekEvaluationQuestion::distinct()->pluck('section')->filter()->values();
        if (!$sections->contains($question->section)) {
            $sections->push($question->section);
        }

        return view('lms::admin-pusat.evaluation-questions.edit', compact('question', 'sections'));
    }

    /**
     * Perbarui pertanyaan kuesioner.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $question = BimtekEvaluationQuestion::findOrFail($id);

        $validated = $request->validate([
            'question'    => 'required|string|max:1000',
            'description' => 'nullable|string|max:1000',
            'type'        => 'required|in:scale,text',
            'min_label'   => 'nullable|string|max:100',
            'max_label'   => 'nullable|string|max:100',
            'section'     => 'required|string|max:150',
            'order'       => 'required|integer|min:1',
            'is_required' => 'boolean',
            'is_active'   => 'boolean',
        ], [
            'question.required' => 'Teks pertanyaan kuesioner wajib diisi.',
            'section.required'  => 'Kelompok/Bagian pertanyaan wajib ditentukan.',
            'order.required'    => 'Nomor urutan pertanyaan wajib diisi.',
        ]);

        $validated['is_required'] = $request->boolean('is_required');
        $validated['is_active']   = $request->boolean('is_active');
        $validated['scale_max']   = 5;

        if ($validated['type'] === 'text') {
            $validated['min_label'] = null;
            $validated['max_label'] = null;
        }

        $question->update($validated);

        ToastMagic::success('Pertanyaan kuesioner evaluasi berhasil diperbarui!');

        return redirect()->route('admin-pusat.management-course.evaluation-questions.index');
    }

    /**
     * Hapus pertanyaan kuesioner.
     */
    public function destroy(string $id): RedirectResponse
    {
        $question = BimtekEvaluationQuestion::findOrFail($id);
        $question->delete();

        ToastMagic::success('Pertanyaan kuesioner evaluasi berhasil dihapus.');

        return redirect()->route('admin-pusat.management-course.evaluation-questions.index');
    }

    /**
     * Toggle status aktif/non-aktif pertanyaan.
     */
    public function toggleStatus(string $id): RedirectResponse
    {
        $question = BimtekEvaluationQuestion::findOrFail($id);
        $question->is_active = !$question->is_active;
        $question->save();

        ToastMagic::success('Status pertanyaan berhasil diubah menjadi: ' . ($question->is_active ? 'Aktif' : 'Non-Aktif'));

        return redirect()->back();
    }
}
