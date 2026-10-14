<?php

namespace Modules\Faq\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Faq\Models\Faq;

class HelpFaqController extends Controller
{
    /**
     * Daftar FAQ untuk modal Pusat Bantuan. FAQ tampil bila role
     * viewer termasuk targetnya. Super-admin dan admin-pusat
     * melihat semuanya.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = Faq::query()->latest()->limit(50);

        if (! $user->hasRole(['super-admin', 'admin-pusat'])) {
            $query->whereHas('roles', fn ($q) => $q->whereIn('name', $user->getRoleNames()->all()));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', '%' . $search . '%')
                    ->orWhere('answer', 'like', '%' . $search . '%');
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(['id', 'question', 'answer']),
        ]);
    }
}
