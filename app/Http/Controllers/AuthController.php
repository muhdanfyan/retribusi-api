<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Opd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Akun Anda tidak aktif'
            ], 403);
        }

        // Check if OPD user and if their OPD is approved
        if ($user->role === 'opd' && $user->opd) {
            if ($user->opd->status !== 'approved') {
                return response()->json([
                    'message' => 'OPD Anda belum disetujui oleh admin'
                ], 403);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('opd'),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('opd')
        ]);
    }

    /**
     * Update authenticated user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:' . ($user instanceof \App\Models\User ? 'users' : 'taxpayers') . ',email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user->load('opd')
        ]);
    }

    /**
     * Change authenticated user password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password saat ini tidak sesuai'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }

    /**
     * Login citizen (taxpayer) using NIK and Password
     */
    public function citizenLogin(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string',
        ]);

        $taxpayer = \App\Models\Taxpayer::where('nik', $request->nik)->first();

        if (!$taxpayer || !Hash::check($request->password, $taxpayer->password)) {
            return response()->json([
                'message' => 'NIK atau password salah'
            ], 401);
        }

        if (!$taxpayer->is_active) {
            return response()->json([
                'message' => 'Akun Wajib Pajak tidak aktif'
            ], 403);
        }

        $token = $taxpayer->createToken('citizen_token')->plainTextToken;

        return response()->json([
            'user' => $taxpayer,
            'token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Login berhasil (Citizen Mode)',
        ]);
    }

    /**
     * Register a new citizen (taxpayer)
     */
    public function registerCitizen(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|size:16|unique:taxpayers,nik',
            'name' => 'required|string',
            'opd_id' => 'nullable|exists:opds,id',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $taxpayer = \App\Models\Taxpayer::create([
            'nik' => $request->nik,
            'name' => $request->name,
            'opd_id' => $request->opd_id, // Will be null if not provided
            'address' => $request->address,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        $token = $taxpayer->createToken('citizen_token')->plainTextToken;

        return response()->json([
            'user' => $taxpayer,
            'token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Registrasi berhasil',
        ], 201);
    }

    /**
     * Change citizen password
     */
    public function changeCitizenPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $taxpayer = $request->user();

        if (!Hash::check($request->current_password, $taxpayer->password)) {
            return response()->json([
                'message' => 'Password saat ini tidak sesuai'
            ], 422);
        }

        $taxpayer->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }
}
