<?php

namespace App\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class UserModulePreference
 *
 * @property int $id
 * @property int $user_id
 * @property int $module_id
 * @property bool $is_pinned
 * @property int $custom_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class UserModulePreference extends Model
{
    use HasFactory;

    protected $table = 'user_module_preferences';

    protected $fillable = [
        'user_id',
        'module_id',
        'is_pinned',
        'custom_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'custom_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<ErpModule, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(ErpModule::class, 'module_id');
    }
}
