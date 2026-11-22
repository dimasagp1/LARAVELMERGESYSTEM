<?php

namespace App\Modules\Production\Controllers;

use App\Core\Models\Plant;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Production\Models\ProductionLine;
use App\Modules\Production\Models\ProductionMachine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MachineMasterController extends Controller
{
    public function __construct(
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display a listing of machines and lines.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $lineFilter = $request->input('line_id', 'all');

        $query = ProductionMachine::query()->with('line')->orderBy('machine_code');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('machine_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($lineFilter !== 'all') {
            $query->where('production_line_id', $lineFilter);
        }

        $machines = $query->paginate(15)->withQueryString();
        $lines = ProductionLine::withCount('machines')->orderBy('line_number')->get();
        $plants = Plant::where('is_active', true)->get(['id', 'code', 'name']);

        return Inertia::render('Modules/Production/Machines/Index', [
            'module' => [
                'name' => 'Produksi & Manufaktur',
                'slug' => 'production',
                'color_theme' => 'rose',
                'menus' => $this->moduleManager->getModuleMenus('production'),
            ],
            'machines' => $machines,
            'lines' => $lines,
            'plants' => $plants,
            'summary' => [
                'total_machines' => ProductionMachine::count(),
                'active_machines' => ProductionMachine::where('is_active', true)->count(),
                'total_lines' => ProductionLine::count(),
            ],
            'filters' => [
                'search' => $search,
                'line_id' => $lineFilter,
            ],
        ]);
    }

    /**
     * Store machine.
     */
    public function storeMachine(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'machine_code' => 'required|string|max:50|unique:production_machines,machine_code',
            'name' => 'required|string|max:100',
            'production_line_id' => 'required|exists:production_lines,id',
            'cycle_time_seconds' => 'required|numeric|min:0.01',
            'status' => 'required|in:running,idle,breakdown,maintenance',
        ]);

        $validated['is_active'] = true;

        ProductionMachine::create($validated);

        return redirect()->back()->with('success', "Mesin {$validated['machine_code']} - {$validated['name']} berhasil ditambahkan.");
    }

    /**
     * Update machine.
     */
    public function updateMachine(Request $request, ProductionMachine $machine): RedirectResponse
    {
        $validated = $request->validate([
            'machine_code' => "required|string|max:50|unique:production_machines,machine_code,{$machine->id}",
            'name' => 'required|string|max:100',
            'production_line_id' => 'required|exists:production_lines,id',
            'cycle_time_seconds' => 'required|numeric|min:0.01',
            'status' => 'required|in:running,idle,breakdown,maintenance',
            'is_active' => 'boolean',
        ]);

        $machine->update($validated);

        return redirect()->back()->with('success', "Mesin {$machine->machine_code} berhasil diperbarui.");
    }

    /**
     * Delete machine.
     */
    public function destroyMachine(ProductionMachine $machine): RedirectResponse
    {
        $code = $machine->machine_code;
        $machine->delete();

        return redirect()->back()->with('success', "Mesin {$code} berhasil dihapus.");
    }

    /**
     * Store line.
     */
    public function storeLine(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'line_number' => 'required|string|max:20|unique:production_lines,line_number',
            'name' => 'required|string|max:100',
            'design_capacity_hourly' => 'required|integer|min:1',
        ]);

        $validated['is_active'] = true;

        ProductionLine::create($validated);

        return redirect()->back()->with('success', "Jalur Produksi {$validated['line_number']} berhasil ditambahkan.");
    }

    /**
     * Update line.
     */
    public function updateLine(Request $request, ProductionLine $line): RedirectResponse
    {
        $validated = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'line_number' => "required|string|max:20|unique:production_lines,line_number,{$line->id}",
            'name' => 'required|string|max:100',
            'design_capacity_hourly' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $line->update($validated);

        return redirect()->back()->with('success', "Jalur Produksi {$line->line_number} berhasil diperbarui.");
    }

    /**
     * Delete line.
     */
    public function destroyLine(ProductionLine $line): RedirectResponse
    {
        if ($line->machines()->exists()) {
            return redirect()->back()->with('error', "Jalur {$line->line_number} tidak dapat dihapus karena masih memiliki mesin terhubung.");
        }

        $line->delete();

        return redirect()->back()->with('success', 'Jalur Produksi berhasil dihapus.');
    }

    /**
     * Export machines to CSV.
     */
    public function export(): StreamedResponse
    {
        $machines = ProductionMachine::with('line')->orderBy('machine_code')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Master_Mesin_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($machines) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode Mesin', 'Nama Mesin', 'Jalur / Line', 'Cycle Time (Detik)', 'Status Operasional', 'Status']);

            foreach ($machines as $m) {
                fputcsv($handle, [
                    $m->machine_code,
                    $m->name,
                    $m->line?->name ?? '-',
                    $m->cycle_time_seconds,
                    strtoupper($m->status),
                    $m->is_active ? 'Aktif' : 'Nonaktif',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
