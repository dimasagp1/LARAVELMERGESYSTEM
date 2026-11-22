<?php

namespace App\Core\Integrations\Events;

use App\Core\Models\Bom;
use App\Models\User;
use App\Modules\Rnd\Models\Formula;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FormulaPromotedToBomEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Formula $formula,
        public Bom $bom,
        public ?User $user = null
    ) {}
}
