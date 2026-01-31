<?php

namespace App\Http\Controllers;

use App\Models\RetributionType;
use App\Models\Taxpayer;
use App\Models\TaxObject;
use App\Models\Bill;
use App\Models\Verification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CitizenServiceController extends Controller
{
    /**
     * List all active services with registration count for the logged-in citizen
     */
    public function index(Request $request)
    {
        $taxpayer = $request->user();
        
        $services = RetributionType::where('is_active', true)
            ->with('opd:id,name,code')
            ->orderBy('name')
            ->get()
            ->map(function ($service) use ($taxpayer) {
                $objects = TaxObject::where('taxpayer_id', $taxpayer->id)
                    ->where('retribution_type_id', $service->id)
                    ->get();
                
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category,
                    'icon' => $service->icon,
                    'base_amount' => $service->base_amount,
                    'unit' => $service->unit,
                    'opd' => $service->opd,
                    'object_count' => $objects->count(),
                    'active_objects_count' => $objects->where('status', 'active')->count(),
                    'form_schema' => $service->form_schema,
                    'requirements' => $service->requirements,
                ];
            });

        return response()->json(['data' => $services]);
    }

    /**
     * Get service detail with list of objects and bills
     */
    public function show(Request $request, $id)
    {
        $taxpayer = $request->user();
        
        $service = RetributionType::with('opd:id,name,code')->findOrFail($id);
        
        $objects = TaxObject::where('taxpayer_id', $taxpayer->id)
            ->where('retribution_type_id', $service->id)
            ->with('zone')
            ->get();
        
        $objectIds = $objects->pluck('id');
        
        $bills = Bill::whereIn('tax_object_id', $objectIds)
            ->orWhere(function($q) use ($taxpayer, $service) {
                // Backward compatibility for old bills linked directly to taxpayer
                $q->where('taxpayer_id', $taxpayer->id)
                  ->where('retribution_type_id', $service->id)
                  ->whereNull('tax_object_id');
            })
            ->with('opd:id,name')
            ->latest()
            ->get();

        return response()->json([
            'data' => [
                'id' => $service->id,
                'name' => $service->name,
                'category' => $service->category,
                'icon' => $service->icon,
                'base_amount' => $service->base_amount,
                'unit' => $service->unit,
                'opd' => $service->opd,
                'form_schema' => $service->form_schema,
                'requirements' => $service->requirements,
                'objects' => $objects,
                'bills' => $bills,
            ]
        ]);
    }

    /**
     * Register a new tax object for a service
     */
    public function register(Request $request, $id)
    {
        $taxpayer = $request->user();
        $service = RetributionType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'zone_id' => 'nullable|exists:zones,id',
            'metadata' => 'nullable',
        ]);

        $cloudinary = app(\App\Services\CloudinaryService::class);
        $metadata = $request->input('metadata', []);
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?: [];
        }

        // Handle dynamic document uploads based on requirements
        $requirements = $service->requirements ?: [];
        foreach ($requirements as $req) {
            $key = $req['key'] ?? null;
            if ($key && $request->hasFile($key)) {
                $metadata[$key] = $cloudinary->upload(
                    $request->file($key), 
                    'citizen/documents/' . $service->id
                );
            }
        }

        // Backward compatibility for old hardcoded fields if they exist in request but not in requirements
        if ($request->hasFile('foto_lokasi_open_kamera') && !isset($metadata['foto_lokasi_open_kamera'])) {
            $metadata['foto_lokasi_open_kamera'] = $cloudinary->upload($request->file('foto_lokasi_open_kamera'), 'taxpayers/survey');
        }
        
        if ($request->hasFile('formulir_data_dukung') && !isset($metadata['formulir_data_dukung'])) {
            $metadata['formulir_data_dukung'] = $cloudinary->upload($request->file('formulir_data_dukung'), 'taxpayers/docs');
        }

        // Create the tax object
        $taxObject = TaxObject::create([
            'taxpayer_id' => $taxpayer->id,
            'retribution_type_id' => $service->id,
            'opd_id' => $service->opd_id,
            'zone_id' => $request->zone_id,
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'metadata' => $metadata,
            'status' => 'pending',
        ]);

        // Create a verification record for the admin to review
        $verification = Verification::create([
            'opd_id' => $service->opd_id,
            'taxpayer_id' => $taxpayer->id,
            'tax_object_id' => $taxObject->id,
            'document_number' => 'REG-' . strtoupper(uniqid()),
            'taxpayer_name' => $taxpayer->name,
            'type' => 'Pendaftaran Objek',
            'amount' => 0, // Registration usually 0 or fixed admin fee
            'status' => 'pending',
            'submitted_at' => Carbon::now(),
            'notes' => 'Pendaftaran unit baru: ' . $taxObject->name,
        ]);

        // Update taxpayer's opd_id if not set (primary affiliation)
        if (!$taxpayer->opd_id) {
            $taxpayer->update(['opd_id' => $service->opd_id]);
        }

        return response()->json([
            'message' => 'Pendaftaran unit berhasil dikirim dan menunggu verifikasi',
            'data' => [
                'object' => $taxObject,
                'verification_id' => $verification->id,
            ]
        ], 201);
    }

    /**
     * Get bills for a specific service (aggregated across all objects)
     */
    public function bills(Request $request, $id)
    {
        $taxpayer = $request->user();
        $service = RetributionType::findOrFail($id);
        
        $objectIds = TaxObject::where('taxpayer_id', $taxpayer->id)
            ->where('retribution_type_id', $service->id)
            ->pluck('id');

        $bills = Bill::whereIn('tax_object_id', $objectIds)
            ->orWhere(function($q) use ($taxpayer, $service) {
                $q->where('taxpayer_id', $taxpayer->id)
                  ->where('retribution_type_id', $service->id)
                  ->whereNull('tax_object_id');
            })
            ->with(['opd:id,name', 'retributionType:id,name,icon', 'taxObject'])
            ->latest()
            ->get();

        return response()->json(['data' => $bills]);
    }
}
