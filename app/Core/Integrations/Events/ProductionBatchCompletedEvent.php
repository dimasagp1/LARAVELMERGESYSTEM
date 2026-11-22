<?php

namespace App\Core\Integrations\Events;

use App\Models\User;
use App\Modules\Production\Models\LaporanHarian;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductionBatchCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LaporanHarian $laporanHarian,
        public ?User $user = null
    ) {}
}
