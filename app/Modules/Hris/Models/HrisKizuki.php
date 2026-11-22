<?php

namespace App\Modules\Hris\Models;

use App\Core\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrisKizuki extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hris_kizukis';

    protected $guarded = ['id'];

    protected $casts = [
        'score' => 'integer',
        'points_rewarded' => 'integer',
        'reviewed_at' => 'datetime',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'proposed_idea',
        'expected_benefit',
        'reward_points',
        'quality_score',
        'cost_saving_score',
        'delivery_score',
        'safety_score',
        'morale_score',
        'review_notes',
    ];

    public function getProposedIdeaAttribute(): string
    {
        return $this->attributes['proposed_improvement'] ?? '';
    }

    public function setProposedIdeaAttribute($value): void
    {
        $this->attributes['proposed_improvement'] = $value;
    }

    public function getExpectedBenefitAttribute(): string
    {
        return $this->attributes['expected_impact'] ?? '';
    }

    public function setExpectedBenefitAttribute($value): void
    {
        $this->attributes['expected_impact'] = $value;
    }

    public function getRewardPointsAttribute(): int
    {
        return (int) ($this->attributes['points_rewarded'] ?? 0);
    }

    public function setRewardPointsAttribute($value): void
    {
        $this->attributes['points_rewarded'] = (int) $value;
    }

    public function getQualityScoreAttribute(): int
    {
        return (int) ($this->custom_attributes['quality_score'] ?? 0);
    }

    public function setQualityScoreAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['quality_score'] = (int) $value;
        $this->custom_attributes = $attrs;
    }

    public function getCostSavingScoreAttribute(): int
    {
        return (int) ($this->custom_attributes['cost_saving_score'] ?? 0);
    }

    public function setCostSavingScoreAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['cost_saving_score'] = (int) $value;
        $this->custom_attributes = $attrs;
    }

    public function getDeliveryScoreAttribute(): int
    {
        return (int) ($this->custom_attributes['delivery_score'] ?? 0);
    }

    public function setDeliveryScoreAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['delivery_score'] = (int) $value;
        $this->custom_attributes = $attrs;
    }

    public function getSafetyScoreAttribute(): int
    {
        return (int) ($this->custom_attributes['safety_score'] ?? 0);
    }

    public function setSafetyScoreAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['safety_score'] = (int) $value;
        $this->custom_attributes = $attrs;
    }

    public function getMoraleScoreAttribute(): int
    {
        return (int) ($this->custom_attributes['morale_score'] ?? 0);
    }

    public function setMoraleScoreAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['morale_score'] = (int) $value;
        $this->custom_attributes = $attrs;
    }

    public function getReviewNotesAttribute(): ?string
    {
        return $this->custom_attributes['review_notes'] ?? null;
    }

    public function setReviewNotesAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['review_notes'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrisEmployee::class, 'employee_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
