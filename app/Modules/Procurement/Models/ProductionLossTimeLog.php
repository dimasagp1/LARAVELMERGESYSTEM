<?php

namespace App\Modules\Procurement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionLossTimeLog extends Model
{
    use HasFactory;

    protected $table = 'production_loss_time_logs';

    protected $guarded = ['id'];

    protected $casts = [
        'log_date' => 'date',
        'downtime_hours' => 'decimal:2',
        'output_per_hour' => 'decimal:2',
        'product_unit_value' => 'decimal:2',
        'loss_value' => 'decimal:2',
        'target_revenue' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calculateLossValue(): void
    {
        $this->loss_value = (float) $this->downtime_hours * (float) $this->output_per_hour * (float) $this->product_unit_value;
    }
}
