<?php

namespace App\Core\Integrations\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FinanceMetricsPublishedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $metrics
     */
    public function __construct(
        public int $periodId,
        public array $metrics,
        public int $updatedObjectivesCount
    ) {}
}
