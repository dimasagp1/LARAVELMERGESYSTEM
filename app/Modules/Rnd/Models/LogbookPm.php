<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Uom;
use App\Core\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LogbookPm extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'rnd_logbook_pms';

    protected $guarded = ['id'];

    protected $casts = [
        'received_date' => 'date',
        'qty_received' => 'decimal:4',
        'document_checklist_json' => 'array',
        'om_verified_at' => 'datetime',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('rnd_logbook_pm');
    }

    public function trialPm(): BelongsTo
    {
        return $this->belongsTo(TrialPm::class, 'trial_pm_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    public function omVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'om_verified_by');
    }
}
