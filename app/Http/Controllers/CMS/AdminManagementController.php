<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->middleware('super.admin')->except([
            'showChangePassword',
            'updatePassword',
        ]);
    }

    /**
     * Display list of all admins
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Admin::query();

        // Search by name or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        $admins = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($admins);
        }

        return view('cms.admin-management.index', compact('admins'));
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        $roles = $this->getRoleOptions();
        return view('cms.admin-management.create', compact('roles'));
    }

    /**
     * Store new admin
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,validator,scanner',
            'can_scan' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;
        $validated['can_scan'] = $validated['can_scan'] ?? false;

        Admin::create($validated);

        return redirect()->route('cms.admin-management.index')
            ->with('success', 'Admin berhasil ditambahkan');
    }

    /**
     * Show edit form
     */
    public function edit(Admin $admin): View
    {
        // Routes ini sudah protected oleh super.admin middleware,
        // jadi super admin bisa edit siapa saja termasuk dirinya sendiri
        $roles = $this->getRoleOptions();
        return view('cms.admin-management.edit', compact('admin', 'roles'));
    }

    /**
     * Update admin data
     */
    public function update(Request $request, Admin $admin): RedirectResponse
    {

        $validated['can_scan'] = $validated['can_scan'] ?? false;

        $admin->update($validated);

        return redirect()->route('cms.admin-management.index')
            ->with('success', 'Admin berhasil diperbarui');
    }

    /**
     * Toggle admin active status
     */
    public function toggleActive(Request $request, Admin $admin): RedirectResponse|JsonResponse
    {
        // Prevent disabling super admin
        if ($admin->role === 'super_admin' && !$admin->is_active) {
            return redirect()->back()->with('error', 'Tidak dapat menonaktifkan super admin');
        }

        // Prevent super admin from disabling themselves
        if (auth('admin')->id() === $admin->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri');
        }

        $admin->update(['is_active' => !$admin->is_active]);

        $message = $admin->is_active ? 'Admin diaktifkan kembali' : 'Admin berhasil dinonaktifkan';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $admin->is_active,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Show change password form
     */
    public function showChangePassword(Admin $admin): View
    {
        // Only super admin or the admin themselves can change password
        if (auth('admin')->id() !== $admin->id && !auth('admin')->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('cms.admin-management.change-password', compact('admin'));
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request, Admin $admin): RedirectResponse
    {
        // Only super admin or the admin themselves can change password
        if (auth('admin')->id() !== $admin->id && !auth('admin')->user()->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengubah password admin lain');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin->update(['password' => Hash::make($validated['password'])]);

        $redirectRoute = auth('admin')->id() === $admin->id
            ? 'cms.dashboard'
            : 'cms.admin-management.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Password berhasil diperbarui');
    }

    /**
     * Delete admin (soft delete)
     */
    public function destroy(Admin $admin): RedirectResponse
    {
        // Prevent deleting super admin
        if ($admin->role === 'super_admin') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus super admin');
        }

        // Prevent super admin from deleting themselves
        if (auth('admin')->id() === $admin->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri');
        }

        $admin->delete();

        return redirect()->route('cms.admin-management.index')
            ->with('success', 'Admin berhasil dihapus');
    }

    /**
     * Get available roles
     */
    private function getRoleOptions(): array
    {
        return [
            'super_admin' => 'Super Admin',
            'validator' => 'Validator',
            'scanner' => 'Scanner',
        ];
    }
}
