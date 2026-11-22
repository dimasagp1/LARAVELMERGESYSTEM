<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinCoaAccount extends Model
{
    use HasFactory;

    protected $table = 'fin_coa_accounts';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_header' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('account_code');
    }

    public function fsli(): BelongsTo
    {
        return $this->belongsTo(FinFsli::class, 'fsli_id');
    }

    public function glEntries(): HasMany
    {
        return $this->hasMany(FinGlEntry::class, 'account_code', 'account_code');
    }

    public function adjLines(): HasMany
    {
        return $this->hasMany(FinAdjJournalLine::class, 'coa_account_id');
    }
}
