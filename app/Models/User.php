<?php

namespace App\Models;

use App\Core\Models\ApprovalLog;
use App\Core\Models\Department;
use App\Core\Models\UserAccessPermission;
use App\Core\Models\UserModulePreference;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property int|null $department_id
 * @property string $status
 * @property string|null $signature_path
 * @property string|null $face_descriptor
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'status',
        'signature_path',
        'face_descriptor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'face_descriptor',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'department_id', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return HasMany<UserModulePreference, $this>
     */
    public function modulePreferences(): HasMany
    {
        return $this->hasMany(UserModulePreference::class);
    }

    /**
     * @return HasMany<ApprovalLog, $this>
     */
    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class);
    }

    /**
     * @return HasMany<UserAccessPermission, $this>
     */
    public function accessPermissions(): HasMany
    {
        return $this->hasMany(UserAccessPermission::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasAnyRole(['superadmin', 'super_admin']) || $this->email === 'admin@herbatech.com';
    }

    public function hasModuleAccess(string $moduleSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $hasAnyConfigured = $this->accessPermissions()->exists();
        if (! $hasAnyConfigured) {
            return true;
        }

        return $this->accessPermissions()
            ->where('module_slug', $moduleSlug)
            ->where('can_view', true)
            ->exists();
    }

    public function hasMenuAccess(string $moduleSlug, string $menuRoute): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $hasAnyConfigured = $this->accessPermissions()->exists();
        if (! $hasAnyConfigured) {
            return true;
        }

        // Check explicit menu route permission
        $menuPerm = $this->accessPermissions()
            ->where('module_slug', $moduleSlug)
            ->where('menu_route', $menuRoute)
            ->first();

        if ($menuPerm) {
            return (bool) $menuPerm->can_view;
        }

        // If specific menus are configured for this module, unlisted menus are not accessible
        $hasMenuRestrictions = $this->accessPermissions()
            ->where('module_slug', $moduleSlug)
            ->whereNotNull('menu_route')
            ->exists();

        if ($hasMenuRestrictions) {
            return false;
        }

        // If only module-level permission is set without granular menu rows, fall back to module access
        return $this->hasModuleAccess($moduleSlug);
    }

    /**
     * Check if user is QA or Management personnel for quality oversight.
     */
    public function isQaOrManagement(): bool
    {
        return $this->hasAnyRole(['qa', 'superadmin', 'head_of_quality', 'operational_manager', 'general_manager'])
            || in_array(strtolower($this->department?->name ?? ''), ['qa', 'quality assurance', 'management', 'direksi']);
    }
}
