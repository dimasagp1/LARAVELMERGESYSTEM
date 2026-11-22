<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinFsli extends Model
{
    use HasFactory;

    protected $table = 'fin_fsli';

    protected $guarded = ['id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function coaAccounts(): HasMany
    {
        return $this->hasMany(FinCoaAccount::class, 'fsli_id');
    }

    public function glEntries(): HasMany
    {
        return $this->hasMany(FinGlEntry::class, 'fsli_id');
    }
}
