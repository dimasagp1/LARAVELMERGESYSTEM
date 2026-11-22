<?php

namespace App\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Bom
 *
 * @property int $id
 * @property string $bom_number
 * @property int $product_id
 * @property string $version
 * @property float $batch_size
 * @property int $batch_uom_id
 * @property string $status
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Bom extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'boms';

    protected $fillable = [
        'bom_number',
        'product_id',
        'version',
        'batch_size',
        'batch_uom_id',
        'status',
        'is_active',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'batch_size' => 'decimal:4',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'bom_number',
                'product_id',
                'version',
                'batch_size',
                'status',
                'is_active',
                'approved_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<Uom, $this>
     */
    public function batchUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'batch_uom_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return HasMany<BomItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class, 'bom_id')->orderBy('sort_order');
    }
}
