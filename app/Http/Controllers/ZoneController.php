<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        return response()->json(Zone::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:zones',
            'multiplier' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $zone = Zone::create($request->all());

        return response()->json($zone, 201);
    }

    public function show(Zone $zone)
    {
        return response()->json($zone);
    }

    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:10|unique:zones,code,' . $zone->id,
            'multiplier' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $zone->update($request->all());

        return response()->json($zone);
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();
        return response()->json(['message' => 'Zona berhasil dihapus']);
    }
}
