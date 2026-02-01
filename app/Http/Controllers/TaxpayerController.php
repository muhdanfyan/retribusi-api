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
        $query = Taxpayer::with(['opd', 'retributionTypes', 'retributionClassifications', 'creator']);

        // Admin OPD and Petugas only see their own OPD's taxpayers
        if ($user && in_array($user->role, ['opd', 'petugas'])) {
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
        \Log::info('Taxpayer store request', $request->all());
        $user = $request->user();
        $cloudinary = app(\App\Services\CloudinaryService::class);

        $request->validate([
            'nik' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'district' => 'nullable|string|max:255',
            'sub_district' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'npwpd' => 'nullable|string|max:50',
            'object_name' => 'nullable|string|max:255',
            'object_address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'sometimes',
            'retribution_type_ids' => 'required|array|min:1',
            'retribution_type_ids.*' => 'exists:retribution_types,id',
            'retribution_classification_ids' => 'nullable|array',
            'retribution_classification_ids.*' => 'exists:retribution_classifications,id',
            'metadata' => 'nullable',
            'foto_lokasi_open_kamera' => 'nullable|image|max:5120',
            'formulir_data_dukung' => 'nullable|file|max:10240',
        ]);

        // Use user's OPD for non-super-admins, or require opd_id for super_admin
        if ($user->role === 'super_admin') {
            $request->validate(['opd_id' => 'required|exists:opds,id']);
            $opdId = $request->opd_id;
        } else {
            $opdId = $user->opd_id;
        }

        // Validate that retribution types belong to the same OPD
        $validTypesIds = (array)$request->retribution_type_ids;
        $validTypesCount = RetributionType::where('opd_id', $opdId)
            ->whereIn('id', $validTypesIds)
            ->count();
        
        if ($validTypesCount !== count($validTypesIds)) {
            return response()->json([
                'message' => 'Jenis retribusi harus milik OPD yang sama'
            ], 422);
        }

        // Handle Metadata & Files
        $metadata = $request->input('metadata', []);
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?: [];
        }

        if ($request->hasFile('foto_lokasi_open_kamera')) {
            $metadata['foto_lokasi_open_kamera'] = $cloudinary->upload($request->file('foto_lokasi_open_kamera'), 'taxpayers/survey');
        }
        
        if ($request->hasFile('formulir_data_dukung')) {
            $metadata['formulir_data_dukung'] = $cloudinary->upload($request->file('formulir_data_dukung'), 'taxpayers/docs');
        }

        $taxpayer = Taxpayer::create([
            'opd_id' => $opdId,
            'nik' => $request->nik,
            'name' => $request->name,
            'address' => $request->address,
            'district' => $request->district,
            'sub_district' => $request->sub_district,
            'phone' => $request->phone,
            'npwpd' => $request->npwpd,
            'object_name' => $request->object_name,
            'object_address' => $request->object_address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => $request->boolean('is_active', true),
            'metadata' => $metadata,
            'created_by' => $user->id,
        ]);

        // Attach retribution types and classifications
        $typeIds = $validTypesIds;
        $classificationIds = (array)$request->input('retribution_classification_ids', []);
        
        foreach ($typeIds as $typeId) {
            // Get classification IDs that belong to this type
            $typeClassifications = \App\Models\RetributionClassification::where('retribution_type_id', $typeId)
                ->whereIn('id', $classificationIds)
                ->pluck('id')
                ->toArray();

            if (empty($typeClassifications)) {
                $taxpayer->retributionTypes()->attach($typeId, ['retribution_classification_id' => null]);
            } else {
                foreach ($typeClassifications as $cId) {
                    $taxpayer->retributionTypes()->attach($typeId, ['retribution_classification_id' => $cId]);
                }
            }
        }

        return response()->json([
            'message' => 'Wajib pajak berhasil ditambahkan',
            'data' => $taxpayer->load(['opd', 'retributionTypes', 'retributionClassifications', 'creator'])
        ], 201);
    }

    /**
     * Show single taxpayer
     */
    public function show(Request $request, Taxpayer $taxpayer)
    {
        $user = $request->user();

        // All non-super-admins (OPD, Kasir) can only view their own OPD's taxpayers
        if (!$user->isSuperAdmin() && $taxpayer->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $taxpayer->load(['opd', 'retributionTypes', 'retributionClassifications', 'creator'])
        ]);
    }

    /**
     * Update taxpayer
     */
    public function update(Request $request, Taxpayer $taxpayer)
    {
        \Log::info('Taxpayer update request for ID: ' . $taxpayer->id, $request->all());
        $user = $request->user();
        $cloudinary = app(\App\Services\CloudinaryService::class);

        // All non-super-admins can only update their own OPD's taxpayers
        if ($user->role !== 'super_admin' && $taxpayer->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'nik' => 'nullable|string|max:20',
                'name' => 'sometimes|string|max:255',
                'address' => 'nullable|string',
                'district' => 'nullable|string|max:255',
                'sub_district' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'npwpd' => 'nullable|string|max:50',
                'object_name' => 'nullable|string|max:255',
                'object_address' => 'nullable|string|max:255',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'is_active' => 'sometimes',
                'retribution_type_ids' => 'sometimes|array|min:1',
                'retribution_type_ids.*' => 'exists:retribution_types,id',
                'retribution_classification_ids' => 'sometimes|array',
                'retribution_classification_ids.*' => 'exists:retribution_classifications,id',
                'metadata' => 'nullable',
                'foto_lokasi_open_kamera' => 'nullable|image|max:5120',
                'formulir_data_dukung' => 'nullable|file|max:10240',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Taxpayer update validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        $data = $request->only([
            'nik', 'name', 'address', 'district', 'sub_district', 'phone', 'npwpd', 
            'object_name', 'object_address', 'latitude', 'longitude', 'is_active'
        ]);

        // Handle Metadata & Files
        $metadata = $request->input('metadata', $taxpayer->metadata ?: []);
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?: [];
        }

        if ($request->hasFile('foto_lokasi_open_kamera')) {
            $metadata['foto_lokasi_open_kamera'] = $cloudinary->upload($request->file('foto_lokasi_open_kamera'), 'taxpayers/survey');
        }
        
        if ($request->hasFile('formulir_data_dukung')) {
            $metadata['formulir_data_dukung'] = $cloudinary->upload($request->file('formulir_data_dukung'), 'taxpayers/docs');
        }

        $data['metadata'] = $metadata;
        $taxpayer->update($data);

        // Update retribution types if provided
        if ($request->has('retribution_type_ids')) {
            $opdId = $taxpayer->opd_id;
            $typeIds = (array)$request->retribution_type_ids;
            
            // Validate that retribution types belong to the same OPD
            $validTypes = RetributionType::where('opd_id', $opdId)
                ->whereIn('id', $typeIds)
                ->count();
            
            if ($validTypes !== count($typeIds)) {
                return response()->json([
                    'message' => 'Jenis retribusi harus milik OPD yang sama'
                ], 422);
            }

            $taxpayer->retributionTypes()->detach();
            
            $classificationIds = (array)$request->input('retribution_classification_ids', []);

            foreach ($typeIds as $typeId) {
                $typeClassifications = \App\Models\RetributionClassification::where('retribution_type_id', $typeId)
                    ->whereIn('id', $classificationIds)
                    ->pluck('id')
                    ->toArray();

                if (empty($typeClassifications)) {
                    $taxpayer->retributionTypes()->attach($typeId, ['retribution_classification_id' => null]);
                } else {
                    foreach ($typeClassifications as $cId) {
                        $taxpayer->retributionTypes()->attach($typeId, ['retribution_classification_id' => $cId]);
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Wajib pajak berhasil diupdate',
            'data' => $taxpayer->fresh()->load(['opd', 'retributionTypes', 'retributionClassifications', 'creator'])
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
