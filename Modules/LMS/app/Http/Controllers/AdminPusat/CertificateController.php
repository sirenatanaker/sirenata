<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LMS\Http\Requests\CertificateStoreRequest;
use Modules\LMS\Http\Requests\CertificateUpdateRequest;
use Modules\LMS\Models\CertificateSetting;
use Modules\LMS\Services\CertificateService;

class CertificateController extends Controller
{
    public function __construct(
        private CertificateService $certificateService
    ) {}

    public function index(Request $request): View
    {
        $settings = $this->certificateService->paginateSettings(
            search: $request->input('search'),
            status: $request->input('status'),
            perPage: (int) $request->input('per_page', 10),
        );

        return view('lms::admin-pusat.certificates.index', compact('settings'));
    }

    public function store(CertificateStoreRequest $request): RedirectResponse
    {
        $this->certificateService->createSetting($request->validated());
        ToastMagic::success('Tanda tangan sertifikat berhasil ditambahkan.');

        return redirect()->route('admin-pusat.certificates.index');
    }

    public function update(CertificateUpdateRequest $request, CertificateSetting $certificate): RedirectResponse
    {
        $this->certificateService->updateSetting($certificate, $request->validated());
        ToastMagic::success('Tanda tangan sertifikat berhasil diperbarui.');

        return redirect()->route('admin-pusat.certificates.index');
    }

    public function activate(CertificateSetting $certificate): RedirectResponse
    {
        $this->certificateService->activateSetting($certificate);
        ToastMagic::success('Tanda tangan berhasil diaktifkan.');

        return redirect()->route('admin-pusat.certificates.index');
    }

    public function destroy(CertificateSetting $certificate): RedirectResponse
    {
        $this->certificateService->deleteSetting($certificate);
        ToastMagic::success('Tanda tangan sertifikat berhasil dihapus.');

        return redirect()->route('admin-pusat.certificates.index');
    }
}
