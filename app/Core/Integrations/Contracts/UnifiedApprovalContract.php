<?php

namespace App\Core\Integrations\Contracts;

use App\Models\User;

interface UnifiedApprovalContract
{
    /**
     * Get aggregated pending approvals from all 8 core modules and custom modules.
     *
     * @return list<array<string, mixed>>
     */
    public function getPendingApprovalsForUser(?User $user = null): array;

    /**
     * Get live badge counts per module slug.
     *
     * @return array<string, int>
     */
    public function getLiveModuleBadgeCounts(): array;
}
