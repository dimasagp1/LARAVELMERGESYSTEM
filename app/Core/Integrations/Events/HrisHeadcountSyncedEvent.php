<?php

namespace App\Core\Integrations\Events;

use App\Modules\Finance\Models\FinHeadcount;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HrisHeadcountSyncedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public FinHeadcount $headcount,
        public int $periodId
    ) {}
}
