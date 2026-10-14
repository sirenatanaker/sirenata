<?php

namespace Modules\Faq\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Faq\Exports\FaqExport;
use Modules\Faq\Models\Faq;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Maatwebsite\Excel\Facades\Excel;

class FaqController extends Controller
{
    protected string $routePrefix = 'super-admin.faq.';

    public function export(Request $request)
    {
        $filename = 'FAQ' . '-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(
            new FaqExport(
                search: $request->string('search')->toString() ?: null,
                role: $request->string('role')->toString() ?: null,
            ),
            $filename
        );
    }

    public function index(Request $request)
    {
        $query = Faq::with(['creator', 'roles'])->latest();

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('question', 'like', $searchTerm)
                    ->orWhere('answer', 'like', $searchTerm);
            });
        }

        $targetRoles = Faq::targetableRoles();

        if ($request->filled('role') && in_array($request->role, $targetRoles, true)) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }

        $faqs = $query->paginate($request->get('per_page', 10))->withQueryString();
        $routePrefix = $this->routePrefix;

        return view('faq::index', compact('faqs', 'routePrefix', 'targetRoles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'roles' => 'required|array|min:1',
            'roles.*' => ['required', 'string', 'exists:roles,name', Rule::notIn(['super-admin', 'admin-pusat'])],
        ]);

        $faq = Faq::create([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'created_by' => Auth::id(),
        ]);
        $faq->roles()->sync(Faq::resolveRoleIds($validated['roles']));

        ToastMagic::success('FAQ berhasil dibuat!');
        return redirect()->route($this->routePrefix . 'index');
    }

    public function show($id)
    {
        $faq = Faq::with(['creator', 'roles'])->findOrFail($id);
        $routePrefix = $this->routePrefix;
        return view('faq::show', compact('faq', 'routePrefix'));
    }

    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'roles' => 'required|array|min:1',
            'roles.*' => ['required', 'string', 'exists:roles,name', Rule::notIn(['super-admin', 'admin-pusat'])],
        ]);

        $faq->update([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
        ]);
        $faq->roles()->sync(Faq::resolveRoleIds($validated['roles']));

        ToastMagic::success('FAQ berhasil diperbarui!');
        return redirect()->route($this->routePrefix . 'index');
    }

    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        ToastMagic::success('FAQ berhasil dihapus!');
        return redirect()->route($this->routePrefix . 'index');
    }
}
