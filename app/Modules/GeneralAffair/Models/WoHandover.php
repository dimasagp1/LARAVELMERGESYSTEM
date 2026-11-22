<?php

namespace App\Modules\GeneralAffair\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WoHandover extends Model
{
    use HasFactory;

    protected $table = 'ga_wo_handovers';

    protected $fillable = [
        'work_order_id',
        'bast_number',
        'handover_date',
        'prepared_by_user_id',
        'client_user_id',
        'satisfaction_rating',
        'client_feedback',
        'client_signature_path',
        'is_confirmed',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'handover_date' => 'date',
            'satisfaction_rating' => 'integer',
            'is_confirmed' => 'boolean',
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WorkOrder, $this>
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }
}
