<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FormulaApproval extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'rnd_formula_approvals';

    protected $guarded = ['id'];

    protected $casts = [
        'gm_approved_at' => 'datetime',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('rnd_formula_approval');
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class, 'formula_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function gmApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gm_approved_by');
    }
}
