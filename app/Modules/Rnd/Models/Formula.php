<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Formula extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'rnd_formulas';

    protected $guarded = ['id'];

    protected $casts = [
        'version' => 'integer',
        'target_serving_size_g' => 'decimal:4',
        'servings_per_pack' => 'integer',
        'total_percentage' => 'decimal:4',
        'total_cost_per_kg' => 'decimal:2',
        'total_cost_per_pack' => 'decimal:2',
        'locked_at' => 'datetime',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('rnd_formula');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function parentFormula(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_formula_id');
    }

    public function childFormulas(): HasMany
    {
        return $this->hasMany(self::class, 'parent_formula_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FormulaItem::class, 'formula_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function omApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_om');
    }

    public function gmApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_gm');
    }

    public function trialRms(): HasMany
    {
        return $this->hasMany(TrialRm::class, 'formula_id');
    }

    public function formulaApproval(): HasOne
    {
        return $this->hasOne(FormulaApproval::class, 'formula_id');
    }

    public function isLocked(): bool
    {
        return $this->status === 'approved' || $this->locked_at !== null;
    }
}
