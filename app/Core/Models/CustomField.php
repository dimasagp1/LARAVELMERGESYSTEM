<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class CustomField
 *
 * @property int $id
 * @property int $entity_id
 * @property string $field_key
 * @property string $label
 * @property string $field_type
 * @property array|null $options_json
 * @property string|null $validation_rules
 * @property bool $is_required
 * @property bool $show_in_table
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class CustomField extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'custom_fields';

    protected $fillable = [
        'entity_id',
        'field_key',
        'label',
        'field_type',
        'options_json',
        'validation_rules',
        'is_required',
        'show_in_table',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options_json' => 'array',
            'is_required' => 'boolean',
            'show_in_table' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<CustomEntity, $this>
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(CustomEntity::class, 'entity_id');
    }
}
