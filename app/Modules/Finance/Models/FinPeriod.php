<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinPeriod extends Model
{
    use HasFactory;

    protected $table = 'fin_periods';

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FinFiscalYear::class, 'fiscal_year_id');
    }

    public function glEntries(): HasMany
    {
        return $this->hasMany(FinGlEntry::class, 'period_id');
    }

    public function adjJournals(): HasMany
    {
        return $this->hasMany(FinAdjJournal::class, 'period_id');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(FinMonthlyBudget::class, 'period_id');
    }

    public function ratioValues(): HasMany
    {
        return $this->hasMany(FinRatioValue::class, 'period_id');
    }

    public function headcount(): HasOne
    {
        return $this->hasOne(FinHeadcount::class, 'period_id');
    }

    public function paidInCapital(): HasOne
    {
        return $this->hasOne(FinPaidInCapital::class, 'period_id');
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(FinSourceReconciliation::class, 'period_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(FinPublication::class, 'period_id');
    }
}
