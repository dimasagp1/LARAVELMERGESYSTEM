<?php

namespace App\Modules\Hris\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrisLeaveCategory extends Model
{
    use HasFactory;

    protected $table = 'hris_leave_categories';

    protected $guarded = ['id'];

    protected $casts = [
        'is_paid' => 'boolean',
        'requires_attachment' => 'boolean',
    ];

    protected $appends = ['quota_days', 'is_deduct_annual'];

    public function getQuotaDaysAttribute(): int
    {
        return (int) ($this->default_quota ?? 12);
    }

    public function setQuotaDaysAttribute($value): void
    {
        $this->attributes['default_quota'] = (int) $value;
    }

    public function getIsDeductAnnualAttribute(): bool
    {
        return $this->code === 'LV-ANNUAL' || $this->code === 'ANNUAL';
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(HrisLeave::class, 'leave_category_id');
    }
}
