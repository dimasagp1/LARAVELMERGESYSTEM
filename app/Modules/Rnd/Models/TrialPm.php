<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TrialPm extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'rnd_trial_pms';

    protected $guarded = ['id'];

    protected $casts = [
        'packaging_specs_json' => 'array',
        'machine_params_json' => 'array',
        'photos_json' => 'array',
        'om_approved_at' => 'datetime',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('rnd_trial_pm');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function omApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'om_approved_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TrialPmApproval::class, 'trial_pm_id');
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(LogbookPm::class, 'trial_pm_id');
    }

    public function isFullyDepartmentApproved(): bool
    {
        $requiredDepts = ['R&D', 'QC', 'Production', 'Engineering'];
        $approvedCount = $this->approvals()
            ->whereIn('department_name', $requiredDepts)
            ->where('status', 'approved')
            ->count();

        return $approvedCount === count($requiredDepts);
    }
}
