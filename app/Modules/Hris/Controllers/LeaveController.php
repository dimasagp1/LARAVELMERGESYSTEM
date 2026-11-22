<?php

namespace App\Modules\Hris\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisLeave;
use App\Modules\Hris\Models\HrisLeaveCategory;
use App\Modules\Hris\Services\HrisNumberingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public function __construct(
        protected HrisNumberingService $numberingService
    ) {}

    /**
     * Display a listing of leave applications.
     */
    public function index(Request $request): Response
    {
        $query = HrisLeave::with([
            'employee.user',
            'employee.department',
            'category',
            'spvApprover',
            'hrApprover',
            'substituteEmployee.user',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('leave_number', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('employee.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->input('leave_category_id')) {
            $query->where('leave_category_id', $categoryId);
        }

        $leaves = $query->latest('id')->paginate(15)->withQueryString();

        return Inertia::render('Modules/Hris/Leave/Index', [
            'leaves' => $leaves,
            'categories' => HrisLeaveCategory::all(),
            'filters' => $request->only(['search', 'status', 'leave_category_id']),
            'summary' => [
                'pending' => HrisLeave::whereIn('status', ['pending_spv', 'pending_hr'])->count(),
                'approved' => HrisLeave::where('status', 'approved')->count(),
                'rejected' => HrisLeave::where('status', 'rejected')->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new leave request.
     */
    public function create(): Response
    {
        return Inertia::render('Modules/Hris/Leave/Form', [
            'employees' => HrisEmployee::with('user')->where('status', 'active')->get(),
            'categories' => HrisLeaveCategory::all(),
        ]);
    }

    /**
     * Store a newly created leave request in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'leave_category_id' => 'required|exists:hris_leave_categories,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'emergency_phone' => 'nullable|string|max:50',
            'substitute_employee_id' => 'nullable|exists:hris_employees,id',
            'attachment_path' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = $startDate->diffInDaysFiltered(fn (Carbon $date) => ! $date->isWeekend(), $endDate) + 1;

        DB::transaction(function () use ($validated, $totalDays) {
            $leaveNumber = $this->numberingService->generateLeaveNumber();

            HrisLeave::create([
                'leave_number' => $leaveNumber,
                'employee_id' => $validated['employee_id'],
                'leave_category_id' => $validated['leave_category_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_days' => max(1, $totalDays),
                'reason' => $validated['reason'],
                'emergency_phone' => $validated['emergency_phone'] ?? null,
                'substitute_employee_id' => $validated['substitute_employee_id'] ?? null,
                'status' => 'pending_spv',
                'attachment_path' => $validated['attachment_path'] ?? null,
            ]);
        });

        return redirect()->route('hris.leaves.index')
            ->with('success', 'Permohonan Cuti/Izin berhasil diajukan dan menunggu approval atasan.');
    }

    /**
     * Approve or reject a leave request.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $leave = HrisLeave::with(['employee', 'category'])->findOrFail($id);

        $action = $request->input('action'); // approve_spv, approve_hr, reject
        $rejectionReason = $request->input('rejection_reason');

        DB::transaction(function () use ($leave, $action, $rejectionReason) {
            $authUserId = auth()->id() ?? 1;

            if ($action === 'approve_spv') {
                $leave->update([
                    'status' => 'pending_hr',
                ]);
            } elseif ($action === 'approve_hr') {
                $leave->update([
                    'approved_by' => $authUserId,
                    'approved_at' => Carbon::now(),
                    'status' => 'approved',
                ]);

                // Deduct annual leave balance if applicable
                if ($leave->category && $leave->category->is_deduct_annual && $leave->employee) {
                    $leave->employee->leave_balance = max(0, $leave->employee->leave_balance - $leave->total_days);
                    $leave->employee->save();
                }
            } elseif ($action === 'reject') {
                $leave->update([
                    'status' => 'rejected',
                    'rejection_reason' => $rejectionReason ?? 'Ditolak oleh atasan/HRD.',
                ]);
            }
        });

        return redirect()->back()->with('success', 'Status permohonan cuti berhasil diperbarui.');
    }

    /**
     * Export Leave Form to PDF.
     */
    public function exportPdf(int $id): HttpResponse
    {
        $leave = HrisLeave::with([
            'employee.user',
            'employee.department',
            'category',
            'spvApprover',
            'hrApprover',
            'substituteEmployee.user',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.hris_leave', compact('leave'));

        return $pdf->stream("Form-Cuti-{$leave->leave_number}.pdf");
    }
}
