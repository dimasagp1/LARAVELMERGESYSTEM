<?php

namespace App\Modules\Hris\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisOvertime;
use App\Modules\Hris\Models\HrisShift;
use App\Modules\Hris\Services\HrisNumberingService;
use App\Modules\Hris\Services\OvertimeTimeBankService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OvertimeController extends Controller
{
    public function __construct(
        protected HrisNumberingService $numberingService,
        protected OvertimeTimeBankService $overtimeService
    ) {}

    /**
     * Display a listing of SPL (Surat Perintah Lembur).
     */
    public function index(Request $request): Response
    {
        $query = HrisOvertime::with([
            'employee.user',
            'employee.department',
            'supervisor',
            'operationsManager',
            'generalManager',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('spl_number', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('work_target', 'like', "%{$search}%")
                    ->orWhereHas('employee.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('compensation_type')) {
            $query->where('compensation_type', $type);
        }

        $overtimes = $query->latest('date')->paginate(15)->withQueryString();

        return Inertia::render('Modules/Hris/Overtime/Index', [
            'overtimes' => $overtimes,
            'filters' => $request->only(['search', 'status', 'compensation_type']),
            'summary' => [
                'pending' => HrisOvertime::whereIn('status', ['pending_spv', 'pending_om', 'pending_gm'])->count(),
                'approved' => HrisOvertime::where('status', 'approved')->count(),
                'total_hours' => round(HrisOvertime::where('status', 'approved')->sum('total_hours'), 1),
            ],
        ]);
    }

    /**
     * Show the form for creating a new overtime request (SPL).
     */
    public function create(): Response
    {
        return Inertia::render('Modules/Hris/Overtime/Form', [
            'employees' => HrisEmployee::with(['user', 'department'])->where('status', 'active')->get(),
            'shifts' => HrisShift::where('is_active', true)->get(),
        ]);
    }

    /**
     * Store a newly created overtime order (SPL) in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'is_holiday' => 'required|boolean',
            'reason' => 'required|string|max:500',
            'work_target' => 'nullable|string|max:500',
            'compensation_type' => 'required|in:cash,time_bank',
        ]);

        $employee = HrisEmployee::findOrFail($validated['employee_id']);

        $startTime = Carbon::parse($validated['date'].' '.$validated['start_time']);
        $endTime = Carbon::parse($validated['date'].' '.$validated['end_time']);
        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }

        $rawHours = round($startTime->diffInMinutes($endTime) / 60, 2);

        $calc = $this->overtimeService->calculateDepnakerOvertime(
            totalHours: $rawHours,
            isHoliday: (bool) $validated['is_holiday'],
            basicSalary: (float) $employee->basic_salary,
            fixedAllowance: (float) $employee->allowance_fixed
        );

        DB::transaction(function () use ($validated, $rawHours, $calc) {
            $splNumber = $this->numberingService->generateSplNumber();

            HrisOvertime::create([
                'spl_number' => $splNumber,
                'employee_id' => $validated['employee_id'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'total_hours' => $rawHours,
                'multiplier_hours' => $calc['multiplier_hours'],
                'rate_per_hour' => $calc['hourly_rate'],
                'total_overtime_pay' => $calc['total_overtime_pay'],
                'is_holiday' => $validated['is_holiday'],
                'compensation_type' => $validated['compensation_type'],
                'reason' => $validated['reason'],
                'work_target' => $validated['work_target'] ?? null,
                'status' => 'pending_spv',
            ]);
        });

        return redirect()->route('hris.overtime.index')
            ->with('success', 'Surat Perintah Lembur (SPL) berhasil diajukan.');
    }

    /**
     * Approve or reject overtime order.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $overtime = HrisOvertime::findOrFail($id);
        $action = $request->input('action'); // approve_spv, approve_om, approve_gm, reject
        $notes = $request->input('notes');

        DB::transaction(function () use ($overtime, $action) {
            $userId = auth()->id() ?? 1;

            if ($action === 'approve_spv') {
                $overtime->update([
                    'status' => 'pending_om',
                ]);
            } elseif ($action === 'approve_om' || $action === 'approve_gm') {
                $overtime->update([
                    'approved_by' => $userId,
                    'approved_at' => Carbon::now(),
                    'status' => 'approved',
                ]);

                // If compensation is time bank, credit to employee's time bank balance
                if ($overtime->compensation_type === 'time_bank') {
                    $this->overtimeService->depositToTimeBank(
                        employeeId: $overtime->employee_id,
                        overtimeId: $overtime->id,
                        hours: (float) $overtime->multiplier_hours,
                        notes: "Kredit lembur SPL #{$overtime->spl_number}"
                    );
                }
            } elseif ($action === 'reject') {
                $overtime->update([
                    'status' => 'rejected',
                ]);
            }
        });

        return redirect()->back()->with('success', 'Status SPL berhasil diperbarui.');
    }

    /**
     * Export SPL to PDF.
     */
    public function exportPdf(int $id): HttpResponse
    {
        $overtime = HrisOvertime::with([
            'employee.user',
            'employee.department',
            'supervisor',
            'operationsManager',
            'generalManager',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.hris_spl', compact('overtime'));

        return $pdf->stream("SPL-{$overtime->spl_number}.pdf");
    }
}
