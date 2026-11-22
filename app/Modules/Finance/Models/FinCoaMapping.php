<?php

namespace App\Modules\Finance\Models;

use App\Core\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinCoaMapping extends Model
{
    use HasFactory;

    protected $table = 'fin_coa_mappings';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fsli(): BelongsTo
    {
        return $this->belongsTo(FinFsli::class, 'fsli_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
