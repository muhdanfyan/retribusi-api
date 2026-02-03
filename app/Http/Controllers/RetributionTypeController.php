<?php

namespace App\Http\Controllers;

use App\Models\RetributionType;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class RetributionTypeController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * List retribution types (OPD-scoped for non-admin users)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = RetributionType::with(['opd', 'classifications']);

        // Admin OPD and Petugas only see their own retribution types
        if ($user && $user->role === 'opd') {
            $query->where('opd_id', $user->opd_id);
        } elseif ($user && $user->role === 'petugas') {
            $query->where('opd_id', $user->opd_id);
            // Filter by assigned types
            $assignedTypeIds = $user->assignments->pluck('retribution_type_id')->unique()->toArray();
            $query->whereIn('id', $assignedTypeIds);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $types = $query->orderBy('name')->get();

        return response()->json(['data' => $types]);
    }

    /**
     * Store new retribution type
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'icon' => 'nullable|file|image|max:1024',
            'base_amount' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        // Use user's OPD for non-super-admins, or require opd_id for super_admin
        if ($user->isSuperAdmin()) {
            $request->validate(['opd_id' => 'required|exists:opds,id']);
            $opdId = $request->opd_id;
        } else {
            $opdId = $user->opd_id;
        }

        $iconUrl = $request->hasFile('icon')
            ? $this->cloudinary->upload($request->file('icon'), 'retribusi/icons')
            : $request->icon;

        $type = RetributionType::create([
            'opd_id' => $opdId,
            'name' => $request->name,
            'category' => $request->category,
            'icon' => $iconUrl,
            'base_amount' => $request->base_amount,
            'unit' => $request->unit,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'message' => 'Jenis retribusi berhasil ditambahkan',
            'data' => $type->load('opd')
        ], 201);
    }

    /**
     * Show single retribution type
     */
    public function show(Request $request, RetributionType $retributionType)
    {
        $user = $request->user();

        // All non-super-admins can only view their own OPD's types
        if (!$user->isSuperAdmin() && $retributionType->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $retributionType->load(['opd', 'taxpayers'])
        ]);
    }

    /**
     * Update retribution type
     */
    public function update(Request $request, RetributionType $retributionType)
    {
        $user = $request->user();

        // All non-super-admins can only update their own OPD's types
        if (!$user->isSuperAdmin() && $retributionType->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->only([
            'name', 'category', 'base_amount', 'unit', 'is_active'
        ]);

        if ($request->hasFile('icon')) {
            // Delete old icon if replaced
            if ($retributionType->icon && filter_var($retributionType->icon, FILTER_VALIDATE_URL) && str_contains($retributionType->icon, 'cloudinary')) {
                $this->cloudinary->delete($retributionType->icon);
            }
            $data['icon'] = $this->cloudinary->upload($request->file('icon'), 'retribusi/icons');
        } elseif ($request->has('icon')) {
            $data['icon'] = $request->icon;
        }

        $retributionType->update($data);

        return response()->json([
            'message' => 'Jenis retribusi berhasil diupdate',
            'data' => $retributionType->fresh()->load('opd')
        ]);
    }

    /**
     * Delete retribution type
     */
    public function destroy(Request $request, RetributionType $retributionType)
    {
        $user = $request->user();

        // All non-super-admins can only delete their own OPD's types
        if (!$user->isSuperAdmin() && $retributionType->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $retributionType->delete();

        return response()->json([
            'message' => 'Jenis retribusi berhasil dihapus'
        ]);
    }
}
