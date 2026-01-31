<?php

namespace App\Http\Controllers;

use App\Models\RetributionClassification;
use Illuminate\Http\Request;

class RetributionClassificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = RetributionClassification::with(['opd', 'retributionType']);

        if ($user && in_array($user->role, ['opd', 'kasir'])) {
            $query->where('opd_id', $user->opd_id);
        } elseif ($request->has('opd_id')) {
            $query->where('opd_id', $request->opd_id);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'retribution_type_id' => 'required|exists:retribution_types,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'description' => 'nullable|string',
        ]);

        $opdId = in_array($user->role, ['opd', 'kasir']) ? $user->opd_id : $request->opd_id;
        
        if (!$opdId && $user->role === 'super_admin') {
            $request->validate(['opd_id' => 'required|exists:opds,id']);
            $opdId = $request->opd_id;
        }

        $classification = RetributionClassification::create([
            'opd_id' => $opdId,
            'retribution_type_id' => $request->retribution_type_id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Klasifikasi berhasil ditambahkan',
            'data' => $classification->load(['opd', 'retributionType'])
        ], 201);
    }

    public function show(RetributionClassification $retributionClassification)
    {
        return response()->json(['data' => $retributionClassification->load(['opd', 'retributionType', 'zones', 'rates'])]);
    }

    public function update(Request $request, RetributionClassification $retributionClassification)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin' && $retributionClassification->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'retribution_type_id' => 'sometimes|exists:retribution_types,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:10',
            'description' => 'nullable|string',
        ]);

        $retributionClassification->update($request->all());

        return response()->json([
            'message' => 'Klasifikasi berhasil diupdate',
            'data' => $retributionClassification->load(['opd', 'retributionType'])
        ]);
    }

    public function destroy(RetributionClassification $retributionClassification)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin' && $retributionClassification->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $retributionClassification->delete();
        return response()->json(['message' => 'Klasifikasi berhasil dihapus']);
    }
}
