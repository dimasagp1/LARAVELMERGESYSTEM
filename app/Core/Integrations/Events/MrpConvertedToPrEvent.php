<?php

namespace App\Core\Integrations\Events;

use App\Models\User;
use App\Modules\Procurement\Models\PurchaseRequest;
use App\Modules\SupplyChain\Models\ScmMrpRun;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MrpConvertedToPrEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  list<int>  $mrpItemIds
     */
    public function __construct(
        public ScmMrpRun $mrpRun,
        public PurchaseRequest $purchaseRequest,
        public array $mrpItemIds,
        public ?User $user = null
    ) {}
}
