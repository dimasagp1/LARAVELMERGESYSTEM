<?php

namespace App\Modules\GeneralAffair\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FpuMaterial extends Model
{
    use HasFactory;

    protected $table = 'ga_fpu_materials';

    protected $fillable = [
        'fpu_request_id',
        'material_name',
        'specification',
        'quantity',
        'uom',
        'estimated_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<FpuRequest, $this>
     */
    public function fpuRequest(): BelongsTo
    {
        return $this->belongsTo(FpuRequest::class, 'fpu_request_id');
    }
}
