<?php

namespace App\Modules\Hris\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrisShiftRequest extends Model
{
    use HasFactory;

    protected $table = 'hris_shift_requests';

    protected $guarded = ['id'];

    protected $casts = [
        'target_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrisEmployee::class, 'employee_id');
    }

    public function currentShift(): BelongsTo
    {
        return $this->belongsTo(HrisShift::class, 'current_shift_id');
    }

    public function requestedShift(): BelongsTo
    {
        return $this->belongsTo(HrisShift::class, 'requested_shift_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
