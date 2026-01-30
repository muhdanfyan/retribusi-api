<?php

namespace App\Http\Controllers;

use App\Models\Taxpayer;
use App\Models\RetributionType;
use Illuminate\Http\Request;

class TaxpayerController extends Controller
{
    /**
     * List taxpayers (OPD-scoped for non-admin users)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Taxpayer::with(['opd', 'retributionTypes']);

        // All non-super-admins only see their own OPD's taxpayers
        if (!$user->isSuperAdmin() && $user->opd_id) {
            $query->where('opd_id', $user->opd_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('npwpd', 'like', "%{$search}%");
            });
        }

        $taxpayers = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json($taxpayers);
    }

    /**
     * Store new taxpayer with retribution types
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nik' => 'required|string|size:16',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'npwpd' => 'nullable|string|max:50',
            'object_name' => 'nullable|string|max:255',
            'object_address' => 'nullable|string',
            'is_active' => 'boolean',
            'retribution_type_ids' => 'required|array|min:1',
            'retribution_type_ids.*' => 'exists:retribution_types,id',
        ]);

        // Use user's OPD for non-super-admins, or require opd_id for super_admin
        if ($user->isSuperAdmin()) {
            $request->validate(['opd_id' => 'required|exists:opds,id']);
            $opdId = $request->opd_id;
        } else {
            $opdId = $user->opd_id;
        }

        // Validate that retribution types belong to the same OPD
        $validTypes = RetributionType::where('opd_id', $opdId)
            ->whereIn('id', $request->retribution_type_ids)
            ->count();
        
        if ($validTypes !== count($request->retribution_type_ids)) {
            return response()->json([
                'message' => 'Jenis retribusi harus milik OPD yang sama'
            ], 422);
        }

        $taxpayer = Taxpayer::create([
            'opd_id' => $opdId,
            'nik' => $request->nik,
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'npwpd' => $request->npwpd,
            'object_name' => $request->object_name,
            'object_address' => $request->object_address,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Attach retribution types
        $taxpayer->retributionTypes()->attach($request->retribution_type_ids);

        return response()->json([
            'message' => 'Wajib pajak berhasil ditambahkan',
            'data' => $taxpayer->load(['opd', 'retributionTypes'])
        ], 201);
    }

    /**
     * Show single taxpayer
     */
    public function show(Request $request, Taxpayer $taxpayer)
    {
        $user = $request->user();

        // All non-super-admins can only view their own OPD's taxpayers
        if (!$user->isSuperAdmin() && $taxpayer->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $taxpayer->load(['opd', 'retributionTypes'])
        ]);
    }

    /**
     * Update taxpayer
     */
    public function update(Request $request, Taxpayer $taxpayer)
    {
        $user = $request->user();

        // All non-super-admins can only update their own OPD's taxpayers
        if (!$user->isSuperAdmin() && $taxpayer->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'nik' => 'sometimes|string|size:16',
            'name' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'npwpd' => 'nullable|string|max:50',
            'object_name' => 'nullable|string|max:255',
            'object_address' => 'nullable|string',
            'is_active' => 'boolean',
            'retribution_type_ids' => 'sometimes|array|min:1',
            'retribution_type_ids.*' => 'exists:retribution_types,id',
        ]);

        $taxpayer->update($request->only([
            'nik', 'name', 'address', 'phone', 'npwpd', 
            'object_name', 'object_address', 'is_active'
        ]));

        // Update retribution types if provided
        if ($request->has('retribution_type_ids')) {
            $opdId = $taxpayer->opd_id;
            
            // Validate that retribution types belong to the same OPD
            $validTypes = RetributionType::where('opd_id', $opdId)
                ->whereIn('id', $request->retribution_type_ids)
                ->count();
            
            if ($validTypes !== count($request->retribution_type_ids)) {
                return response()->json([
                    'message' => 'Jenis retribusi harus milik OPD yang sama'
                ], 422);
            }

            $taxpayer->retributionTypes()->sync($request->retribution_type_ids);
        }

        return response()->json([
            'message' => 'Wajib pajak berhasil diupdate',
            'data' => $taxpayer->fresh()->load(['opd', 'retributionTypes'])
        ]);
    }

    /**
     * Delete taxpayer
     */
    public function destroy(Request $request, Taxpayer $taxpayer)
    {
        $user = $request->user();

        // All non-super-admins can only delete their own OPD's taxpayers
        if (!$user->isSuperAdmin() && $taxpayer->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $taxpayer->delete();

        return response()->json([
            'message' => 'Wajib pajak berhasil dihapus'
        ]);
    }
}
