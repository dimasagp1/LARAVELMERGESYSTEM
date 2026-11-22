<?php

namespace App\Http\Controllers;

use App\Core\Models\Department;
use App\Core\Services\ModuleManagerService;
use App\Core\Services\UserPermissionService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserAccessController extends Controller
{
    public function __construct(
        protected UserPermissionService $permissionService,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display list of users with their roles, departments, and access indicators.
     */
    public function index(Request $request): Response
    {
        $query = User::with(['department', 'roles', 'accessPermissions'])
            ->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $allModulesCount = $this->moduleManager->getActiveModules()->count();

        $users = $query->paginate(12)->through(function (User $user) use ($allModulesCount) {
            $isSuperAdmin = $user->isSuperAdmin();
            $perms = $user->accessPermissions;
            $hasRestrictions = $perms->isNotEmpty();

            $allowedModuleCount = $isSuperAdmin || ! $hasRestrictions
                ? $allModulesCount
                : $perms->whereNull('menu_route')->where('can_view', true)->count();

            $allowedMenuCount = $isSuperAdmin || ! $hasRestrictions
                ? 'Semua'
                : $perms->whereNotNull('menu_route')->where('can_view', true)->count();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status ?? 'active',
                'department' => $user->department?->name ?? 'Lintas Departemen',
                'department_id' => $user->department_id,
                'roles' => $user->getRoleNames(),
                'is_superadmin' => $isSuperAdmin,
                'has_restrictions' => $hasRestrictions,
                'allowed_module_count' => $allowedModuleCount,
                'allowed_menu_count' => $allowedMenuCount,
                'all_modules_count' => $allModulesCount,
                'created_at' => $user->created_at?->translatedFormat('d M Y') ?? '-',
            ];
        })->withQueryString();

        return Inertia::render('Settings/Users/Index', [
            'users' => $users,
            'departments' => Department::where('is_active', true)->get(['id', 'code', 'name']),
            'roles' => Role::all()->pluck('name'),
            'filters' => $request->only(['search', 'department_id', 'status']),
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'superadmins' => User::where(function ($q) {
                    $q->whereHas('roles', fn ($rq) => $rq->whereIn('name', ['superadmin', 'super_admin']))
                        ->orWhere('email', 'admin@herbatech.com');
                })->count(),
                'total_departments' => Department::count(),
            ],
        ]);
    }

    /**
     * Store new user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
            'department_id' => 'nullable|exists:departments,id',
            'role' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'department_id' => $validated['department_id'] ?? null,
            'status' => $validated['status'],
        ]);

        if (! empty($validated['role'])) {
            $role = Role::findOrCreate($validated['role']);
            $user->assignRole($role);
        }

        // Apply default preset
        $preset = $validated['role'] === 'superadmin' ? 'superadmin' : 'all';
        $this->permissionService->applyPreset($user, $preset);

        return back()->with('success', "Pengguna [{$user->name}] berhasil ditambahkan dengan hak akses default.");
    }

    /**
     * Update user details.
     */
    public function update(int $id, Request $request): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'department_id' => 'nullable|exists:departments,id',
            'role' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'department_id' => $validated['department_id'] ?? null,
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return back()->with('success', "Data pengguna [{$user->name}] berhasil diperbarui.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $newStatus = ($user->status === 'active') ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return back()->with('success', "Status akun [{$user->name}] diubah menjadi {$newStatus}.");
    }

    /**
     * Get permission checklist matrix for user (JSON for modal / drawer).
     */
    public function getPermissions(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $data = $this->permissionService->getUserPermissionsMatrix($user);

        return response()->json($data);
    }

    /**
     * Update module and menu checklist permissions for user.
     */
    public function updatePermissions(int $id, Request $request): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'modules' => 'required|array',
            'menus' => 'nullable|array',
        ]);

        $this->permissionService->syncUserPermissions($user, $validated);

        return back()->with('success', "Hak akses modul & menu pengguna [{$user->name}] berhasil disimpan.");
    }

    /**
     * Apply preset checklist permissions to user.
     */
    public function applyPreset(int $id, Request $request): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'preset' => 'required|in:superadmin,operator,finance,qc_qa,clear',
        ]);

        $this->permissionService->applyPreset($user, $validated['preset']);

        $presetNames = [
            'superadmin' => 'Super Administrator (Akses Penuh)',
            'operator' => 'Operator Lapangan (Produksi, SCM, Presensi)',
            'finance' => 'Finance & FAT (Keuangan, PR, Payroll)',
            'qc_qa' => 'QC & QA Lab (QMS, Karantina, Reject)',
            'clear' => 'Kosongkan Seluruh Akses',
        ];

        return back()->with('success', "Preset [{$presetNames[$validated['preset']]}] berhasil diterapkan pada [{$user->name}].");
    }
}
