<?php

namespace App\Modules\GeneralAffair\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecurityShift extends Model
{
    use HasFactory;

    protected $table = 'ga_security_shifts';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
    ];

    /**
     * @return HasMany<SecurityAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(SecurityAssignment::class, 'shift_id');
    }
}
