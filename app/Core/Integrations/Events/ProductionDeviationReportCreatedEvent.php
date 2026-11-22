<?php

namespace App\Core\Integrations\Events;

use App\Models\User;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Quality\Models\Deviation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductionDeviationReportCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LaporanHarian $laporanHarian,
        public Deviation $deviation,
        public ?User $user = null
    ) {}
}
