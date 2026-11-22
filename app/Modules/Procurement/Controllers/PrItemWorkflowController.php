<?php

namespace App\Modules\Procurement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Services\PrWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PrItemWorkflowController extends Controller
{
    public function __construct(
        protected PrWorkflowService $workflowService
    ) {}

    public function updateEstimates(Request $request, int $itemId): RedirectResponse
    {
        $item = PrItem::findOrFail($itemId);

        $validated = $request->validate([
            'estimated_price' => 'required|numeric|min:0',
            'rekap_po_odoo' => 'boolean',
            'is_incoming' => 'boolean',
            'need_holding_quotation' => 'boolean',
            'need_holding_delivery' => 'boolean',
            'is_bon' => 'boolean',
        ]);

        $this->workflowService->updateItemEstimates($item, $validated, $request->user());

        return back()->with('success', "Estimasi harga dan flag alur kerja item {$item->item_name} berhasil disimpan.");
    }

    public function approveL1(Request $request, int $itemId): RedirectResponse
    {
        $item = PrItem::findOrFail($itemId);
        $notes = $request->input('notes');

        $this->workflowService->approveL1($item, $request->user(), $notes);

        return back()->with('success', "Persetujuan Level 1 untuk item {$item->item_name} berhasil.");
    }

    public function submitHoldingQuotation(Request $request, int $itemId): RedirectResponse
    {
        $item = PrItem::findOrFail($itemId);
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'attachment_path' => 'nullable|string',
        ]);

        $this->workflowService->submitHoldingQuotation($item, $validated, $request->user());

        return back()->with('success', 'Penawaran harga dari Holding berhasil dilampirkan.');
    }

    public function inputActualPrice(Request $request, int $itemId): RedirectResponse
    {
        $item = PrItem::findOrFail($itemId);
        $validated = $request->validate([
            'actual_unit_price' => 'nullable|numeric|min:0',
            'actual_price' => 'nullable|numeric|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'vendor_name' => 'nullable|string',
            'delivery_plans' => 'nullable|array',
            'delivery_plans.*.plan_date' => 'required|date',
            'delivery_plans.*.plan_quantity' => 'required|numeric|min:0.01',
            'delivery_plans.*.notes' => 'nullable|string',
        ]);

        $this->workflowService->inputActualPriceAndPlan($item, $validated, $request->user());

        return back()->with('success', "Harga aktual dan rencana kedatangan item {$item->item_name} berhasil ditetapkan.");
    }

    public function approveL2(Request $request, int $itemId): RedirectResponse
    {
        $item = PrItem::findOrFail($itemId);
        $notes = $request->input('notes');

        $this->workflowService->approveL2Gm($item, $request->user(), $notes);

        return back()->with('success', "Persetujuan Level 2 (GM) untuk item {$item->item_name} berhasil. Item siap diterbitkan PO.");
    }

    public function reject(Request $request, int $itemId): RedirectResponse
    {
        $item = PrItem::findOrFail($itemId);
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'step' => 'nullable|string|in:om,gm,proc',
        ]);

        $this->workflowService->rejectItem($item, $request->user(), $validated['reason'], $validated['step'] ?? 'om');

        return back()->with('success', "Item {$item->item_name} telah ditolak dengan catatan evaluasi.");
    }

    public function processPo(Request $request, int $itemId): RedirectResponse
    {
        $item = PrItem::findOrFail($itemId);
        $validated = $request->validate([
            'po_number' => 'nullable|string',
            'po_manual_odoo' => 'nullable|boolean',
            'po_manual_reason' => 'nullable|string',
        ]);

        $this->workflowService->processPo($item, $validated, $request->user());

        return back()->with('success', "Pemesanan PO berhasil diproses untuk item {$item->item_name}.");
    }

    public function sendNote(Request $request, int $itemId): RedirectResponse
    {
        $item = PrItem::findOrFail($itemId);
        $validated = $request->validate([
            'note' => 'required|string|max:500',
        ]);

        $this->workflowService->sendValidationNote($item, $validated['note'], $request->user());

        return back()->with('success', 'Catatan klarifikasi berhasil dikirimkan ke pemohon.');
    }
}
