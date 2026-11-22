<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Models\Product;
use App\Core\Services\ModuleManagerService;
use App\Core\Services\SystemSettingService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\SupplyChain\Models\ScmInventoryStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemApiController extends Controller
{
    public function __construct(
        protected SystemSettingService $settingService,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Get system public configuration and branding.
     */
    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'api_version' => 'v1',
            'timestamp' => now()->toIso8601String(),
            'data' => $this->settingService->getPublicSettings(),
        ]);
    }

    /**
     * Get active ERP modules and contextual navigation menus.
     */
    public function modules(Request $request): JsonResponse
    {
        $user = $request->user();
        $modules = $this->moduleManager->getModulesForUser($user);

        return response()->json([
            'success' => true,
            'count' => $modules->count(),
            'data' => $modules->map(function ($mod) {
                return [
                    'id' => $mod->id,
                    'slug' => $mod->slug,
                    'name' => $mod->name,
                    'description' => $mod->description,
                    'color_theme' => $mod->color_theme,
                    'badge_count' => $mod->badge_count ?? 0,
                    'menus' => $mod->menus ?? [],
                ];
            }),
        ]);
    }

    /**
     * Get list of active ERP users (sanitized for integration).
     */
    public function users(Request $request): JsonResponse
    {
        $users = User::with(['department', 'roles'])
            ->where('status', 'active')
            ->select(['id', 'name', 'email', 'department_id', 'status', 'created_at'])
            ->paginate($request->input('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Product catalog data endpoint for external integration.
     */
    public function products(Request $request): JsonResponse
    {
        $query = Product::with(['uom'])->where('is_active', true);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $products = $query->paginate($request->input('limit', 25));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Multi-warehouse stock balances for external WMS/Supply Chain sync.
     */
    public function inventory(Request $request): JsonResponse
    {
        $query = ScmInventoryStock::with(['product.uom', 'plant', 'location'])
            ->where('quantity', '>', 0);

        if ($plantId = $request->input('plant_id')) {
            $query->where('plant_id', $plantId);
        }

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        $stocks = $query->paginate($request->input('limit', 25));

        return response()->json([
            'success' => true,
            'data' => $stocks,
        ]);
    }

    /**
     * Inbound webhook receiver for external IoT scales, barcode scanners, SAP/Odoo events.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $event = $request->input('event', 'generic_event');

        // Log incoming webhook event in activity log or notification
        activity('api_webhook')
            ->withProperties(['event' => $event, 'payload' => $payload])
            ->log("Menerima webhook eksternal [{$event}]");

        return response()->json([
            'success' => true,
            'received_at' => now()->toIso8601String(),
            'event' => $event,
            'status' => 'acknowledged',
        ]);
    }
}
