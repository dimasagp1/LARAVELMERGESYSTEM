<?php

namespace App\Modules\Hris\Controllers;

use App\Core\Models\Department;
use App\Http\Controllers\Controller;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisKizuki;
use App\Modules\Hris\Services\HrisNumberingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class KizukiController extends Controller
{
    public function __construct(
        protected HrisNumberingService $numberingService
    ) {}

    /**
     * Display a listing of Kaizen Kizuki proposals & gamification leaderboard.
     */
    public function index(Request $request): Response
    {
        $query = HrisKizuki::with(['employee.user', 'department', 'reviewer']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kizuki_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('proposed_idea', 'like', "%{$search}%")
                    ->orWhereHas('employee.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $kizukis = $query->latest('id')->paginate(12)->withQueryString();

        // Leaderboard Top Kaizen Contributors
        $leaderboard = HrisKizuki::query()
            ->join('hris_employees', 'hris_employees.id', '=', 'hris_kizukis.employee_id')
            ->join('users', 'users.id', '=', 'hris_employees.user_id')
            ->select('users.name', 'hris_employees.nik', DB::raw('SUM(hris_kizukis.reward_points) as total_points'), DB::raw('COUNT(hris_kizukis.id) as total_proposals'))
            ->where('hris_kizukis.status', 'implemented')
            ->groupBy('users.name', 'hris_employees.nik')
            ->orderByDesc('total_points')
            ->take(5)
            ->get();

        return Inertia::render('Modules/Hris/Kizuki/Index', [
            'kizukis' => $kizukis,
            'leaderboard' => $leaderboard,
            'filters' => $request->only(['search', 'category', 'status']),
            'departments' => Department::select('id', 'name', 'code')->get(),
            'summary' => [
                'total_submitted' => HrisKizuki::count(),
                'implemented' => HrisKizuki::where('status', 'implemented')->count(),
                'total_points_distributed' => HrisKizuki::sum('reward_points'),
            ],
        ]);
    }

    /**
     * Show the form for submitting a new Kaizen Kizuki.
     */
    public function create(): Response
    {
        return Inertia::render('Modules/Hris/Kizuki/Form', [
            'employees' => HrisEmployee::with('user')->where('status', 'active')->get(),
            'departments' => Department::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Store a newly created Kaizen Kizuki proposal.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'department_id' => 'required|exists:departments,id',
            'title' => 'required|string|max:255',
            'category' => 'required|in:safety,5s,quality,cost,productivity',
            'current_condition' => 'required|string',
            'proposed_idea' => 'required|string',
            'expected_benefit' => 'required|string',
            'before_photo_path' => 'nullable|string',
            'after_photo_path' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $kizukiNumber = $this->numberingService->generateKizukiNumber();

            HrisKizuki::create([
                'kizuki_number' => $kizukiNumber,
                'employee_id' => $validated['employee_id'],
                'department_id' => $validated['department_id'],
                'title' => $validated['title'],
                'category' => $validated['category'],
                'current_condition' => $validated['current_condition'],
                'proposed_idea' => $validated['proposed_idea'],
                'expected_benefit' => $validated['expected_benefit'],
                'before_photo_path' => $validated['before_photo_path'] ?? null,
                'after_photo_path' => $validated['after_photo_path'] ?? null,
                'status' => 'submitted',
            ]);
        });

        return redirect()->route('hris.kizuki.index')
            ->with('success', 'Ide Kaizen Kizuki berhasil disubmit untuk dinilai Tim Komite.');
    }

    /**
     * Review and assign QCDSM scoring + reward points.
     */
    public function review(Request $request, int $id): RedirectResponse
    {
        $kizuki = HrisKizuki::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:under_review,implemented,rejected',
            'review_notes' => 'nullable|string',
            'quality_score' => 'nullable|integer|min:0|max:100',
            'cost_saving_score' => 'nullable|integer|min:0|max:100',
            'delivery_score' => 'nullable|integer|min:0|max:100',
            'safety_score' => 'nullable|integer|min:0|max:100',
            'morale_score' => 'nullable|integer|min:0|max:100',
            'reward_points' => 'nullable|integer|min:0',
        ]);

        $kizuki->update([
            'reviewer_id' => auth()->id() ?? 1,
            'reviewed_at' => Carbon::now(),
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'quality_score' => $validated['quality_score'] ?? 0,
            'cost_saving_score' => $validated['cost_saving_score'] ?? 0,
            'delivery_score' => $validated['delivery_score'] ?? 0,
            'safety_score' => $validated['safety_score'] ?? 0,
            'morale_score' => $validated['morale_score'] ?? 0,
            'reward_points' => $validated['reward_points'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'Penilaian Kaizen Kizuki berhasil disimpan.');
    }
}
