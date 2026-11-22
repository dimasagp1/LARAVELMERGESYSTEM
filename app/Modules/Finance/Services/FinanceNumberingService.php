<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FinAdjJournal;
use App\Modules\Finance\Models\FinPublication;
use Carbon\Carbon;

class FinanceNumberingService
{
    public function generateAjeNumber(?Carbon $date = null): string
    {
        $date = $date ?: Carbon::now();
        $yearMonth = $date->format('Ym');
        $prefix = "AJE-{$yearMonth}-";
        $count = FinAdjJournal::where('journal_number', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%04d', $prefix, $count);
    }

    public function generatePublicationNumber(?Carbon $date = null): string
    {
        $date = $date ?: Carbon::now();
        $yearMonth = $date->format('Ym');
        $prefix = "PUB-{$yearMonth}-";
        $count = FinPublication::where('publication_number', 'like', "{$prefix}%")->count() + 1;

        return sprintf('%s%04d', $prefix, $count);
    }
}
