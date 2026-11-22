<?php

namespace App\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAccessPermission extends Model
{
    use HasFactory;

    protected $table = 'user_access_permissions';

    protected $fillable = [
        'user_id',
        'module_slug',
        'menu_route',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'can_approve',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'can_approve' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
