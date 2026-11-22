<?php

namespace App\Modules\Hris\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrisTimeBank extends Model
{
    use HasFactory;

    protected $table = 'hris_time_banks';

    protected $guarded = ['id'];

    protected $casts = [
        'hours' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    protected $appends = ['balance_hours'];

    public function getBalanceHoursAttribute(): float
    {
        return (float) ($this->balance_after ?? $this->hours ?? 0);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrisEmployee::class, 'employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
