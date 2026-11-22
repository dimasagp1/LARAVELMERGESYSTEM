<?php

namespace App\Modules\Hris\Controllers;

use App\Core\Models\Department;
use App\Http\Controllers\Controller;
use App\Modules\Hris\Models\HrisDepartmentObjective;
use App\Modules\Hris\Models\HrisDepartmentObjectiveAchievement;
use App\Modules\Hris\Models\HrisDepartmentWorkProgram;
use App\Modules\Hris\Services\StrategicBscService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StrategicBscController extends Controller
{
    public function __construct(
        protected StrategicBscService $bscService
    ) {}

    /**
     * Display Strategic Performance BSC Level 3 & Level 4 Matrix.
     */
    public function index(Request $request): Response
    {
        $year = (int) $request->input('year', Carbon::now()->year);

        $query = HrisDepartmentObjective::with(['department', 'workPrograms', 'achievements'])
            ->where('period_year', $year);

        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        if ($perspective = $request->input('perspective')) {
            $query->where('perspective', $perspective);
        }

        $objectives = $query->orderBy('perspective')->get();

        $departments = Department::select('id', 'name', 'code')->get();

        return Inertia::render('Modules/Hris/Strategic/Index', [
            'objectives' => $objectives,
            'selectedYear' => $year,
            'departments' => $departments,
            'filters' => $request->only(['year', 'department_id', 'perspective']),
            'summary' => [
                'total_objectives' => $objectives->count(),
                'avg_achievement' => round($objectives->avg('achievement_percentage') ?? 0, 1),
                'total_programs' => HrisDepartmentWorkProgram::whereHas('departmentObjective', fn ($q) => $q->where('period_year', $year))->count(),
                'locked_count' => $objectives->where('is_locked', true)->count(),
            ],
        ]);
    }

    /**
     * Store a new Department Objective (BSC Level 3).
     */
    public function storeObjective(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'period_year' => 'required|integer|min:2024|max:2030',
            'perspective' => 'required|in:financial,customer,internal_process,learning_growth',
            'kpi_name' => 'required|string|max:255',
            'target_value' => 'required|numeric',
            'target_unit' => 'required|string|max:50',
            'polarity' => 'required|in:maximize,minimize',
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        $dept = Department::find($validated['department_id']);
        $seq = HrisDepartmentObjective::where('department_id', $validated['department_id'])->count() + 1;
        $code = sprintf('OBJ-%s-%02d', strtoupper($dept?->code ?? 'GEN'), $seq);

        $obj = HrisDepartmentObjective::create([
            'department_id' => $validated['department_id'],
            'code' => $code,
            'name' => $validated['kpi_name'],
            'target_formula' => $validated['perspective'],
            'polarity' => $validated['polarity'],
            'weight' => $validated['weight'],
        ]);

        // Create initial achievement record
        HrisDepartmentObjectiveAchievement::create([
            'department_objective_id' => $obj->id,
            'period_month' => (int) now()->month,
            'period_year' => $validated['period_year'],
            'target_value' => $validated['target_value'],
            'actual_value' => 0,
            'achievement_percentage' => 0,
            'status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Sasaran Strategis Departemen (BSC L3) berhasil ditambahkan.');
    }

    /**
     * Store a Work Program (BSC Level 4 Action Item).
     */
    public function storeProgram(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_objective_id' => 'required|exists:hris_department_objectives,id',
            'program_name' => 'required|string|max:255',
            'pic_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'progress_pct' => 'nullable|integer|between:0,100',
        ]);

        $objective = HrisDepartmentObjective::findOrFail($validated['department_objective_id']);

        HrisDepartmentWorkProgram::create([
            'department_id' => $objective->department_id,
            'objective_id' => $objective->id,
            'program_name' => $validated['program_name'],
            'progress_percentage' => $validated['progress_pct'] ?? 0,
            'status' => 'planned',
        ]);

        return redirect()->back()->with('success', 'Program Kerja (BSC L4) berhasil ditambahkan.');
    }

    /**
     * Evaluate actual achievement.
     */
    public function evaluate(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'period_month' => 'required|integer|between:1,12',
            'actual_value' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->bscService->evaluateObjective(
                objectiveId: $id,
                periodMonth: (int) $validated['period_month'],
                actualValue: (float) $validated['actual_value'],
                notes: $validated['notes'] ?? null
            );

            return redirect()->back()->with('success', 'Evaluasi capaian KPI berhasil dihitung.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengevaluasi KPI: '.$e->getMessage());
        }
    }

    /**
     * Lock and push objective to Corporate BSC Dashboard.
     */
    public function pushToBsc(int $id): RedirectResponse
    {
        try {
            $this->bscService->lockAndPushToBsc($id);

            return redirect()->back()->with('success', 'Capaian Sasaran berhasil dikunci dan disinkronkan ke Corporate BSC.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal lock BSC: '.$e->getMessage());
        }
    }
}
