<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Opd;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class OpdController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }
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
            
            // Admin User Data (will also be used as OPD email)
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email|unique:opds,email',
            'admin_password' => ['required', 'confirmed', Password::min(8)],
            'admin_phone' => 'nullable|string|max:20',
            'logo' => 'nullable|file|image|max:2048', // Max 2MB logo
        ]);

        $logoUrl = $request->hasFile('logo') 
            ? $this->cloudinary->upload($request->file('logo'), 'opds/logos')
            : null;

        try {
            DB::beginTransaction();

            // Create OPD with pending status
            $opd = Opd::create([
                'name' => $request->opd_name,
                'code' => strtoupper($request->opd_code),
                'address' => $request->opd_address,
                'phone' => $request->opd_phone,
                'email' => $request->admin_email, // Link to admin email
                'status' => 'pending', // Needs super_admin approval
                'is_active' => true,
                'logo_url' => $logoUrl,
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
            'logo' => 'nullable|file|image|max:2048',
        ]);

        $data = $request->only([
            'name', 'code', 'address', 'phone', 'email', 'status', 'is_active'
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($opd->logo_url) {
                $this->cloudinary->delete($opd->logo_url);
            }
            $data['logo_url'] = $this->cloudinary->upload($request->file('logo'), 'opds/logos');
        }

        if ($request->has('email')) {
            // Also update the associated admin user's email
            User::where('opd_id', $opd->id)
                ->where('role', 'opd')
                ->update(['email' => $request->email]);
        }

        $opd->update($data);

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
