<?php

namespace App\Modules\Procurement\Controllers;

use App\Core\Models\Department;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Procurement\Models\DepartmentBudget;
use App\Modules\Procurement\Models\ExpenseStaging;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetManagementController extends Controller
{
    public function __construct(
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $year = (int) $request->input('fiscal_year', Carbon::now()->format('Y'));
        $month = (int) $request->input('month', Carbon::now()->format('m'));

        $budgets = DepartmentBudget::with('department')
            ->where('fiscal_year', $year)
            ->where('month', $month)
            ->get();

        $expenseStagings = ExpenseStaging::with(['purchaseRequest', 'department', 'item'])
            ->latest('id')
            ->limit(20)
            ->get();

        return Inertia::render('Modules/Procurement/Budget/Index', [
            'module' => [
                'name' => 'Pagu Anggaran FAT',
                'slug' => 'procurement',
                'color_theme' => 'blue',
                'menus' => $this->moduleManager->getModuleMenus('procurement'),
            ],
            'budgets' => $budgets,
            'expenseStagings' => $expenseStagings,
            'departments' => Department::select('id', 'name', 'code')->get(),
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'fiscal_year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
            'purpose' => 'required|string|max:255',
            'initial_budget' => 'required|numeric|min:0',
        ]);

        DepartmentBudget::updateOrCreate(
            [
                'department_id' => $validated['department_id'],
                'fiscal_year' => $validated['fiscal_year'],
                'month' => $validated['month'],
                'purpose' => $validated['purpose'],
            ],
            [
                'initial_budget' => $validated['initial_budget'],
            ]
        );

        return back()->with('success', 'Pagu anggaran departemen berhasil disimpan.');
    }
}
