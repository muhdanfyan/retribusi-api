<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = User::query();

        // If not super_admin, scope by OPD
        if (!$user->isSuperAdmin()) {
            $query->where('opd_id', $user->opd_id);
        }

        $users = $query->with('opd')->orderBy('name')->get()->map(function ($u) {
            $u->department = $u->opd?->name;
            return $u;
        });

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $loggedInUser = $request->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', 'string', Rule::in(['super_admin', 'opd', 'verifikator', 'petugas', 'viewer'])],
            'opd_id' => 'nullable|exists:opds,id',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // If not super_admin, force same opd_id and restrict role elevation
        if (!$loggedInUser->isSuperAdmin()) {
            $validated['opd_id'] = $loggedInUser->opd_id;
            if ($validated['role'] === 'super_admin' || $validated['role'] === 'opd') {
                $validated['role'] = 'viewer'; // Default to lower role if attempted elevation
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'opd_id' => $validated['opd_id'] ?: null,
            'status' => $validated['status'],
        ]);

        $user->load('opd');
        $user->department = $user->opd?->name ?? 'All';

        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user, Request $request)
    {
        $loggedInUser = $request->user();

        if (!$loggedInUser->isSuperAdmin() && $user->opd_id !== $loggedInUser->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $loggedInUser = $request->user();

        if (!$loggedInUser->isSuperAdmin() && $user->opd_id !== $loggedInUser->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['sometimes', 'required', 'string', Rule::in(['super_admin', 'opd', 'verifikator', 'petugas', 'viewer'])],
            'opd_id' => 'nullable|exists:opds,id',
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
        ]);

        if (!$loggedInUser->isSuperAdmin()) {
            unset($validated['opd_id']); // Cannot change OPD
            if (isset($validated['role']) && ($validated['role'] === 'super_admin' || $validated['role'] === 'opd')) {
                unset($validated['role']); // Cannot elevate to opd/super_admin
            }
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if ($user->role === 'opd' && isset($validated['email'])) {
            // Also update the associated OPD email
            \App\Models\Opd::where('id', $user->opd_id)->update(['email' => $validated['email']]);
        }

        $user->load('opd');
        $user->department = $user->opd?->name ?? 'All';

        return response()->json($user);
    }

    /**
 * Remove the specified resource from storage.
 */
public function destroy(User $user, Request $request)
{
    $loggedInUser = $request->user();

    // Validasi: tidak bisa hapus diri sendiri
    if ($user->id === $loggedInUser->id) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak dapat menghapus akun sendiri'
        ], 400);
    }

    // Validasi: super_admin tidak bisa dihapus oleh non-super_admin
    if ($user->role === 'super_admin' && !$loggedInUser->isSuperAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak memiliki izin untuk menghapus Super Admin'
        ], 403);
    }

    // Validasi: OPD user hanya bisa dihapus oleh admin OPD yang sama atau super_admin
    if (!$loggedInUser->isSuperAdmin() && $user->opd_id !== $loggedInUser->opd_id) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak memiliki izin untuk menghapus user dari OPD lain'
        ], 403);
    }

    $userName = $user->name;
    $user->delete();

    return response()->json([
        'success' => true,
        'message' => "User '{$userName}' berhasil dihapus"
    ], 200);
}
}
