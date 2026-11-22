<?php

namespace App\Core\Integrations\Listeners;

use App\Core\Integrations\Events\QcInspectionCompletedEvent;
use App\Core\Integrations\Services\NotificationCenterService;

class UnlockThreeWayMatchingAndStockListener
{
    public function __construct(
        protected NotificationCenterService $notificationService
    ) {}

    public function handle(QcInspectionCompletedEvent $event): void
    {
        $inspection = $event->inspection;
        $item = $inspection->goodsReceiptItem;
        $receipt = $item?->goodsReceipt;
        $poNumber = $receipt?->po_number ?? 'PO-UNKNOWN';

        if ($event->isPassed) {
            $this->notificationService->broadcast(
                moduleSlug: 'finance',
                type: 'qc_passed_ready_for_matching',
                title: "Kelulusan QC Bahan ({$item?->item_name})",
                message: "Bahan dari PO {$poNumber} lulus uji mutu CPOTB. Izin pembuatan tagihan & 3-Way Matching AP terbuka.",
                actionUrl: '/supply-chain/matching',
                urgency: 'normal'
            );
        } else {
            $this->notificationService->broadcast(
                moduleSlug: 'procurement',
                type: 'qc_rejected_vendor_return',
                title: "Penolakan Mutu QC: {$item?->item_name}",
                message: "Material dari PO {$poNumber} gagal uji spesifikasi mutu ({$event->rejectedQty} {$item?->uom}). Memo retur vendor diterbitkan.",
                actionUrl: '/supply-chain/material-return',
                urgency: 'high'
            );
        }
    }
}
