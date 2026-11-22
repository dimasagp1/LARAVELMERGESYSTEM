<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NpdProposal extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'rnd_npd_proposals';

    protected $guarded = ['id'];

    protected $casts = [
        'target_cogs' => 'decimal:2',
        'target_selling_price' => 'decimal:2',
        'development_start' => 'date',
        'development_end' => 'date',
        'project_team_json' => 'array',
        'attachments_json' => 'array',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('rnd_npd_proposal');
    }

    public function prf(): BelongsTo
    {
        return $this->belongsTo(Prf::class, 'prf_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function preformulationStudies(): HasMany
    {
        return $this->hasMany(PreformulationStudy::class, 'npd_proposal_id');
    }

    public function sampleEvaluations(): HasMany
    {
        return $this->hasMany(SampleEvaluation::class, 'npd_proposal_id');
    }
}
