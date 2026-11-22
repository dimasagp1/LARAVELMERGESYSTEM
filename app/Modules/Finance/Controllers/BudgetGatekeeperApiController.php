<?php

namespace App\Modules\Finance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Services\BudgetGatekeeperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetGatekeeperApiController extends Controller
{
    public function __construct(
        protected BudgetGatekeeperService $gatekeeperService
    ) {}

    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|integer|exists:departments,id',
            'requested_amount' => 'required|numeric|min:0',
            'period_id' => 'nullable|integer|exists:fin_periods,id',
        ]);

        $res = $this->gatekeeperService->checkBudget(
            $validated['department_id'],
            (float) $validated['requested_amount'],
            $validated['period_id'] ?? null
        );

        return response()->json($res);
    }

    public function book(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|integer|exists:departments,id',
            'amount' => 'required|numeric|min:0',
            'period_id' => 'nullable|integer|exists:fin_periods,id',
        ]);

        $this->gatekeeperService->bookBudget(
            $validated['department_id'],
            (float) $validated['amount'],
            $validated['period_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Budget successfully booked for department.',
        ]);
    }
}
