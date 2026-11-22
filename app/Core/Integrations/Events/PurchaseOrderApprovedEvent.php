<?php

namespace App\Core\Integrations\Events;

use App\Models\User;
use App\Modules\Procurement\Models\PrItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderApprovedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PrItem $item,
        public User $user
    ) {}
}
