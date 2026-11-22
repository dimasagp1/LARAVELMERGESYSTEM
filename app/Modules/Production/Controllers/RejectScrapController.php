<?php

namespace App\Modules\Production\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Production\Models\RejectDetail;
use App\Modules\Production\Services\RejectScrapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RejectScrapController extends Controller
{
    public function __construct(
        protected RejectScrapService $scrapService
    ) {}

    /**
     * Display list and analytics of Reject & Scrap.
     */
    public function index(Request $request): Response
    {
        $query = RejectDetail::with(['laporanHarian.product', 'laporanHarian.line'])->latest();

        if ($type = $request->input('defect_type')) {
            $query->where('jenis_reject', $type);
        }

        $rejects = $query->paginate(15)->withQueryString();
        $analytics = $this->scrapService->getScrapAnalysis();

        $activeReports = LaporanHarian::with(['product', 'line'])
            ->where('status', '!=', 'locked')
            ->latest('tanggal')
            ->take(20)
            ->get();

        return Inertia::render('Modules/Produksi/Scrap/Index', [
            'rejects' => $rejects,
            'analytics' => $analytics,
            'activeReports' => $activeReports,
            'filters' => $request->only(['defect_type']),
        ]);
    }

    /**
     * Store newly reported reject.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'laporan_harian_id' => 'required|exists:laporan_harians,id',
            'jenis_reject' => 'required|string|max:255',
            'material_name' => 'nullable|string|max:255',
            'jumlah' => 'required|numeric|min:0.01',
            'satuan' => 'required|string|max:20',
            'biaya_satuan' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $this->scrapService->recordReject((int) $validated['laporan_harian_id'], $validated);

        return redirect()->back()->with('success', 'Data reject berhasil dicatat dan disinkronkan ke referensi Odoo Scrap.');
    }
}
