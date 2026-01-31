<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Zone::with(['opd', 'retributionType', 'classification']);

        if ($user && in_array($user->role, ['opd', 'kasir'])) {
            $query->where('opd_id', $user->opd_id);
        } elseif ($request->has('opd_id')) {
            $query->where('opd_id', $request->opd_id);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'opd_id' => 'required|exists:opds,id',
            'retribution_type_id' => 'required|exists:retribution_types,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:zones',
            'multiplier' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $zone = Zone::create($request->all());

        return response()->json($zone->load(['opd', 'retributionType']), 201);
    }

    public function show(Zone $zone)
    {
        return response()->json($zone);
    }

    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'opd_id' => 'sometimes|exists:opds,id',
            'retribution_type_id' => 'sometimes|exists:retribution_types,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:10|unique:zones,code,' . $zone->id,
            'multiplier' => 'sometimes|numeric|min:0',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $zone->update($request->all());

        return response()->json($zone->load(['opd', 'retributionType']));
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();
        return response()->json(['message' => 'Zona berhasil dihapus']);
    }
}
