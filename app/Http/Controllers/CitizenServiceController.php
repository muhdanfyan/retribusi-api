<?php

namespace App\Http\Controllers;

use App\Models\RetributionType;
use App\Models\Taxpayer;
use App\Models\Bill;
use Illuminate\Http\Request;

class CitizenServiceController extends Controller
{
    /**
     * List all active services with registration status for the logged-in citizen
     */
    public function index(Request $request)
    {
        $taxpayer = $request->user();
        
        $services = RetributionType::where('is_active', true)
            ->with('opd:id,name,code')
            ->orderBy('name')
            ->get()
            ->map(function ($service) use ($taxpayer) {
                $registration = $taxpayer->retributionTypes()
                    ->where('retribution_type_id', $service->id)
                    ->first();
                
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category,
                    'icon' => $service->icon,
                    'base_amount' => $service->base_amount,
                    'unit' => $service->unit,
                    'opd' => $service->opd,
                    'is_registered' => $registration !== null,
                    'registration_date' => $registration?->pivot?->created_at,
                ];
            });

        return response()->json(['data' => $services]);
    }

    /**
     * Get service detail with registration status and bills
     */
    public function show(Request $request, $id)
    {
        $taxpayer = $request->user();
        
        $service = RetributionType::with('opd:id,name,code')->findOrFail($id);
        
        $registration = $taxpayer->retributionTypes()
            ->where('retribution_type_id', $service->id)
            ->first();
        
        $bills = [];
        if ($registration) {
            $bills = Bill::where('taxpayer_id', $taxpayer->id)
                ->where('retribution_type_id', $service->id)
                ->with('opd:id,name')
                ->latest()
                ->get();
        }

        return response()->json([
            'data' => [
                'id' => $service->id,
                'name' => $service->name,
                'category' => $service->category,
                'icon' => $service->icon,
                'base_amount' => $service->base_amount,
                'unit' => $service->unit,
                'opd' => $service->opd,
                'is_registered' => $registration !== null,
                'registration_date' => $registration?->pivot?->created_at,
                'custom_amount' => $registration?->pivot?->custom_amount,
                'notes' => $registration?->pivot?->notes,
                'bills' => $bills,
            ]
        ]);
    }

    /**
     * Register citizen for a service
     */
    public function register(Request $request, $id)
    {
        $taxpayer = $request->user();
        
        $service = RetributionType::findOrFail($id);
        
        // Check if already registered
        $existing = $taxpayer->retributionTypes()
            ->where('retribution_type_id', $service->id)
            ->exists();
        
        if ($existing) {
            return response()->json([
                'message' => 'Anda sudah terdaftar pada layanan ini'
            ], 422);
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        // Register the taxpayer for this service
        $taxpayer->retributionTypes()->attach($service->id, [
            'notes' => $request->notes,
            'custom_amount' => null, // Admin can set this later
        ]);

        // Update taxpayer's opd_id if not set
        if (!$taxpayer->opd_id) {
            $taxpayer->update(['opd_id' => $service->opd_id]);
        }

        return response()->json([
            'message' => 'Berhasil mendaftar layanan ' . $service->name,
            'data' => [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'registered_at' => now(),
            ]
        ], 201);
    }

    /**
     * Get bills for a specific service
     */
    public function bills(Request $request, $id)
    {
        $taxpayer = $request->user();
        
        $service = RetributionType::findOrFail($id);
        
        // Check if registered
        $isRegistered = $taxpayer->retributionTypes()
            ->where('retribution_type_id', $service->id)
            ->exists();
        
        if (!$isRegistered) {
            return response()->json([
                'message' => 'Anda belum terdaftar pada layanan ini'
            ], 403);
        }

        $bills = Bill::where('taxpayer_id', $taxpayer->id)
            ->where('retribution_type_id', $service->id)
            ->with(['opd:id,name', 'retributionType:id,name,icon'])
            ->latest()
            ->get();

        return response()->json(['data' => $bills]);
    }
}
