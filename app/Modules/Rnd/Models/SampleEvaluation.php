<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SampleEvaluation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'rnd_sample_evaluations';

    protected $guarded = ['id'];

    protected $casts = [
        'session_date' => 'date',
        'panelist_count' => 'integer',
        'average_taste' => 'decimal:2',
        'average_color' => 'decimal:2',
        'average_aroma' => 'decimal:2',
        'average_texture' => 'decimal:2',
        'average_aftertaste' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('rnd_sample_evaluation');
    }

    public function npdProposal(): BelongsTo
    {
        return $this->belongsTo(NpdProposal::class, 'npd_proposal_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
