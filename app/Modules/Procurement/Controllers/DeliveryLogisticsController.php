<?php

namespace App\Modules\Procurement\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Services\PrWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryLogisticsController extends Controller
{
    public function __construct(
        protected PrWorkflowService $workflowService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $query = PrItem::with([
            'purchaseRequest.department',
            'deliveryPlans',
            'deliveries.receiver',
            'vendor',
        ])->whereIn('status', ['ordered', 'delivered', 'completed'])
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhereHas('purchaseRequest', fn ($pq) => $pq->where('pr_number', 'like', "%{$search}%"));
            });
        }

        $items = $query->paginate(12)->withQueryString();

        return Inertia::render('Modules/Procurement/Delivery/Index', [
            'module' => [
                'name' => 'Logistik & Penerimaan',
                'slug' => 'procurement',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('procurement'),
            ],
            'items' => $items,
            'filters' => $request->only(['search']),
        ]);
    }

    public function recordDelivery(Request $request, int $itemId): RedirectResponse
    {
        $item = PrItem::findOrFail($itemId);

        $validated = $request->validate([
            'delivered_quantity' => 'required|numeric|min:0.01',
            'delivery_date' => 'required|date',
            'delivery_order_number' => 'required|string|max:100',
            'attachment_path' => 'nullable|string',
        ]);

        $this->workflowService->recordDelivery($item, $validated, $request->user());

        return back()->with('success', "Penerimaan fisik barang untuk {$item->item_name} berhasil dicatat.");
    }

    public function recordRetur(Request $request, int $itemId): RedirectResponse
    {
        $item = PrItem::findOrFail($itemId);

        $validated = $request->validate([
            'retur_for_delivery_id' => 'nullable|exists:pr_item_deliveries,id',
            'delivery_date' => 'required|date',
            'rejected_quantity' => 'required|numeric|min:0.01',
            'reject_reason' => 'required|string|max:500',
            'delivery_order_number' => 'nullable|string',
        ]);

        $this->workflowService->recordRetur($item, $validated, $request->user());

        return back()->with('success', "Pencatatan retur/penolakan barang {$item->item_name} berhasil.");
    }
}
