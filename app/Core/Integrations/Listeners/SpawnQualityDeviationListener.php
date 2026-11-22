<?php

namespace App\Core\Integrations\Listeners;

use App\Core\Integrations\Events\ProductionDeviationReportCreatedEvent;
use App\Core\Integrations\Services\NotificationCenterService;

class SpawnQualityDeviationListener
{
    public function __construct(
        protected NotificationCenterService $notificationService
    ) {}

    public function handle(ProductionDeviationReportCreatedEvent $event): void
    {
        $this->notificationService->broadcast(
            moduleSlug: 'quality',
            type: 'deviation_spawned',
            title: "Penyimpangan Kritis Diterima ({$event->deviation->deviation_number})",
            message: "Laporan deviasi dari proses produksi batch {$event->laporanHarian->nomor_bets} telah terdaftar dan siap diinvestigasi.",
            actionUrl: '/quality/deviations',
            urgency: 'high'
        );
    }
}
