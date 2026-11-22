<?php

namespace App\Modules\Hris\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrisMasterGolongan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hris_master_golongans';

    protected $guarded = ['id'];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['name'];

    public function getNameAttribute(): string
    {
        return $this->attributes['grade_name'] ?? '';
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['grade_name'] = $value;
    }

    public function nextGrade(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_grade_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(HrisEmployee::class, 'grade_id');
    }
}
