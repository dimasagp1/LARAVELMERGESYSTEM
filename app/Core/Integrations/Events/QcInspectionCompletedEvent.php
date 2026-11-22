<?php

namespace App\Core\Integrations\Events;

use App\Models\User;
use App\Modules\SupplyChain\Models\ScmQcInspection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QcInspectionCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ScmQcInspection $inspection,
        public User $user,
        public bool $isPassed,
        public float $passedQty,
        public float $rejectedQty
    ) {}
}
