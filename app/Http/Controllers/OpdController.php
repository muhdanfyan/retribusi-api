<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Opd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class OpdController extends Controller
{
    /**
     * Public OPD registration endpoint
     */
    public function register(Request $request)
    {
        $request->validate([
            // OPD Data
            'opd_name' => 'required|string|max:255',
            'opd_code' => 'required|string|max:50|unique:opds,code',
            'opd_address' => 'nullable|string',
            'opd_phone' => 'nullable|string|max:20',
            'opd_email' => 'nullable|email',
            
            // Admin User Data
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => ['required', 'confirmed', Password::min(8)],
            'admin_phone' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // Create OPD with pending status
            $opd = Opd::create([
                'name' => $request->opd_name,
                'code' => strtoupper($request->opd_code),
                'address' => $request->opd_address,
                'phone' => $request->opd_phone,
                'email' => $request->opd_email,
                'status' => 'pending', // Needs super_admin approval
                'is_active' => true,
            ]);

            // Create admin user for the OPD
            $user = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'phone' => $request->admin_phone,
                'role' => 'opd',
                'opd_id' => $opd->id,
                'status' => 'active',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pendaftaran OPD berhasil. Mohon tunggu persetujuan dari admin.',
                'opd' => $opd,
                'user' => $user,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Pendaftaran gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all OPDs (super_admin only)
     */
    public function index(Request $request)
    {
        $query = Opd::with('users');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $opds = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $opds]);
    }

    /**
     * Show single OPD
     */
    public function show(Opd $opd)
    {
        return response()->json([
            'data' => $opd->load(['users', 'retributionTypes'])
        ]);
    }

    /**
     * Update OPD (including approval)
     */
    public function update(Request $request, Opd $opd)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:opds,code,' . $opd->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'status' => 'sometimes|in:pending,approved,rejected',
            'is_active' => 'sometimes|boolean',
        ]);

        $opd->update($request->only([
            'name', 'code', 'address', 'phone', 'email', 'status', 'is_active'
        ]));

        return response()->json([
            'message' => 'OPD berhasil diupdate',
            'data' => $opd->fresh()
        ]);
    }

    /**
     * Delete OPD
     */
    public function destroy(Opd $opd)
    {
        $opd->delete();

        return response()->json([
            'message' => 'OPD berhasil dihapus'
        ]);
    }
}
