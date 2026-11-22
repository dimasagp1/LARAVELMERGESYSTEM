<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Prf extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'rnd_prfs';

    protected $guarded = ['id'];

    protected $casts = [
        'attachments_json' => 'array',
        'custom_attributes' => 'array',
        'target_launch_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('rnd_prf');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function npdProposal(): HasOne
    {
        return $this->hasOne(NpdProposal::class, 'prf_id');
    }
}
