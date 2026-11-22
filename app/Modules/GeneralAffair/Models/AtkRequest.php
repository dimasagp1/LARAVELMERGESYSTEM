<?php

namespace App\Modules\GeneralAffair\Models;

use App\Core\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtkRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ga_atk_requests';

    protected $fillable = [
        'parent_id',
        'request_number',
        'type',
        'user_id',
        'department_id',
        'tanggal_pengajuan',
        'alasan_pengajuan',
        'status',
        'manager_approved_by',
        'manager_approved_at',
        'manager_notes',
        'admin_approved_by',
        'admin_approved_at',
        'admin_notes',
        'diberikan_pada',
        'receiver_name',
        'receiver_signature',
        'custom_attributes',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'manager_approved_at' => 'datetime',
            'admin_approved_at' => 'datetime',
            'diberikan_pada' => 'date',
            'custom_attributes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<AtkRequest, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<AtkRequest, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return HasMany<AtkRequestItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(AtkRequestItem::class, 'request_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function managerApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function adminApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }
}
