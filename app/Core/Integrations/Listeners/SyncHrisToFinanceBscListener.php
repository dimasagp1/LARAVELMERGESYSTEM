<?php

namespace App\Core\Integrations\Listeners;

use App\Core\Integrations\Events\HrisHeadcountSyncedEvent;
use App\Core\Integrations\Services\NotificationCenterService;

class SyncHrisToFinanceBscListener
{
    public function __construct(
        protected NotificationCenterService $notificationService
    ) {}

    public function handle(HrisHeadcountSyncedEvent $event): void
    {
        $this->notificationService->broadcast(
            moduleSlug: 'finance',
            type: 'headcount_synced',
            title: 'Sinkronisasi SDM ke Finansial Sukses',
            message: "Data {$event->headcount->total_headcount} karyawan ({$event->headcount->direct_labor_count} TKL) dan {$event->headcount->working_hours} jam kerja berhasil diperbarui ke 19 Rasio Finansial BSC.",
            actionUrl: '/finance/financial-ratios',
            urgency: 'normal'
        );
    }
}
