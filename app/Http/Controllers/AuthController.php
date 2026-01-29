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
     * Login citizen (taxpayer) using NIK
     */
    public function citizenLogin(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string',
        ]);

        $taxpayer = \App\Models\Taxpayer::where('nik', $request->nik)->first();

        if (!$taxpayer) {
            return response()->json([
                'message' => 'NIK tidak ditemukan'
            ], 401);
        }

        // Validate password (disamakan dengan NIK)
        if ($request->password !== $taxpayer->nik) {
            return response()->json([
                'message' => 'Password salah (Gunakan NIK sebagai password)'
            ], 401);
        }

        if (!$taxpayer->is_active) {
            return response()->json([
                'message' => 'Akun Wajib Pajak tidak aktif'
            ], 403);
        }

        return response()->json([
            'user' => $taxpayer,
            'message' => 'Login berhasil (Citizen Mode)',
        ]);
    }
}
