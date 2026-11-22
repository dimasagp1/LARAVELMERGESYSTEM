<?php

namespace App\Modules\Hris\Controllers;

use App\Core\Models\Department;
use App\Http\Controllers\Controller;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisSalary;
use App\Modules\Hris\Services\PayrollCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollCalculatorService $payrollService
    ) {}

    /**
     * Display a listing of payroll calculation results.
     */
    public function index(Request $request): Response
    {
        $month = (int) $request->input('period_month', Carbon::now()->month);
        $year = (int) $request->input('period_year', Carbon::now()->year);

        $query = HrisSalary::with(['employee.user', 'employee.department', 'employee.golongan'])
            ->where('period_month', $month)
            ->where('period_year', $year);

        if ($deptId = $request->input('department_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $deptId));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $salaries = $query->paginate(20)->withQueryString();

        $allPeriod = HrisSalary::where('period_month', $month)->where('period_year', $year)->get();
        $totalGross = (float) $allPeriod->sum('gross_salary');
        $totalDeductions = (float) $allPeriod->sum('total_deductions');
        $totalNet = (float) $allPeriod->sum('net_salary');

        return Inertia::render('Modules/Hris/Payroll/Index', [
            'salaries' => $salaries,
            'periodMonth' => $month,
            'periodYear' => $year,
            'filters' => $request->only(['period_month', 'period_year', 'department_id', 'status']),
            'summary' => [
                'total_gross' => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net' => $totalNet,
                'processed_count' => $allPeriod->count(),
            ],
            'departments' => Department::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Run batch or selective payroll calculation.
     */
    public function calculate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2024|max:2030',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:hris_employees,id',
        ]);

        $month = (int) $validated['period_month'];
        $year = (int) $validated['period_year'];

        $query = HrisEmployee::where('status', 'active');
        if (! empty($validated['employee_ids'])) {
            $query->whereIn('id', $validated['employee_ids']);
        }
        $employees = $query->get();

        $count = 0;
        foreach ($employees as $emp) {
            $this->payrollService->calculateMonthlySalary($emp, $month, $year);
            $count++;
        }

        return redirect()->route('hris.payroll.index', [
            'period_month' => $month,
            'period_year' => $year,
        ])->with('success', "Proses kalkulasi Payroll PPh 21 TER selesai untuk {$count} karyawan.");
    }

    /**
     * Display a single employee payslip.
     */
    public function show(int $id): Response
    {
        $salary = HrisSalary::with([
            'employee.user',
            'employee.department',
            'employee.golongan',
        ])->findOrFail($id);

        return Inertia::render('Modules/Hris/Payroll/Show', [
            'salary' => $salary,
        ]);
    }

    /**
     * Export employee payslip to PDF.
     */
    public function exportPdf(int $id): HttpResponse
    {
        $salary = HrisSalary::with([
            'employee.user',
            'employee.department',
            'employee.golongan',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.hris_payslip', compact('salary'));

        return $pdf->stream("Payslip-{$salary->salary_number}.pdf");
    }
}
