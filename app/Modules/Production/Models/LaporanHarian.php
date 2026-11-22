<?php

namespace App\Modules\Production\Models;

use App\Core\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanHarian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'laporan_harians';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
        'pause_started_at' => 'datetime',
        'locked_at' => 'datetime',
        'ct' => 'decimal:2',
        'gross_time_menit' => 'integer',
        'waktu_bersih_menit' => 'integer',
        'total_pause_menit' => 'integer',
        'capacity_fisik' => 'integer',
        'output_fisik' => 'integer',
        'total_reject' => 'integer',
        'oee_persen' => 'decimal:2',
        'yield_persen' => 'decimal:2',
        'availability_persen' => 'decimal:2',
        'performance_persen' => 'decimal:2',
        'produktivitas_persen' => 'decimal:2',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'timer_active',
        'is_locked',
        'good_output',
        'loss_time_menit',
    ];

    public function getTimerActiveAttribute(): bool
    {
        return $this->timer_status === 'start';
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->status === 'locked';
    }

    public function getGoodOutputAttribute(): int
    {
        return max(0, ($this->output_fisik ?? 0) - ($this->total_reject ?? 0));
    }

    public function getLossTimeMenitAttribute(): int
    {
        return max(0, ($this->gross_time_menit ?? 0) - ($this->waktu_bersih_menit ?? 0));
    }

    public function weeklyPlan(): BelongsTo
    {
        return $this->belongsTo(WeeklyPlan::class, 'weekly_plan_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    public function produk(): BelongsTo
    {
        return $this->product();
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class, 'line_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(ProductionMachine::class, 'mesin_id');
    }

    public function mesin(): BelongsTo
    {
        return $this->machine();
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function downtimeDetails(): HasMany
    {
        return $this->hasMany(DowntimeDetail::class, 'laporan_harian_id');
    }

    public function rejectDetails(): HasMany
    {
        return $this->hasMany(RejectDetail::class, 'laporan_harian_id');
    }
}
