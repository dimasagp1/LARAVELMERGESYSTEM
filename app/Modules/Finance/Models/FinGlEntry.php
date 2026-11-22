<?php

namespace App\Modules\Finance\Models;

use App\Core\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FinGlEntry extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'fin_gl_entries';

    protected $guarded = ['id'];

    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'signed_amount' => 'decimal:2',
        'is_synced' => 'boolean',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('finance_gl');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(FinPeriod::class, 'period_id');
    }

    public function fsli(): BelongsTo
    {
        return $this->belongsTo(FinFsli::class, 'fsli_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
