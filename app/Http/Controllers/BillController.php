<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Taxpayer;
use App\Models\TaxObject;
use App\Models\RetributionType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillController extends Controller
{
    /**
     * List bills (OPD-scoped)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Bill::with(['retributionType', 'user', 'opd', 'taxObject', 'taxpayer']);

        if ($user && $user->role === 'opd') {
            $query->where('opd_id', $user->opd_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhereHas('taxpayer', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $billings = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($billings);
    }

    /**
     * Generate a single bill
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'tax_object_id' => 'required|exists:tax_objects,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|string',
            'due_date' => 'required|date',
            'metadata' => 'nullable|array',
        ]);

        $taxObject = TaxObject::with('taxpayer')->find($request->tax_object_id);
        
        // Security check
        if (!$user->isSuperAdmin() && $taxObject->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bill = Bill::create([
            'user_id' => $user->id,
            'taxpayer_id' => $taxObject->taxpayer_id,
            'tax_object_id' => $taxObject->id,
            'opd_id' => $taxObject->opd_id,
            'retribution_type_id' => $taxObject->retribution_type_id,
            'bill_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'amount' => $request->amount,
            'status' => 'pending',
            'period' => $request->period,
            'metadata' => $request->metadata,
            'due_date' => $request->due_date,
        ]);

        return response()->json([
            'message' => 'Tagihan berhasil dibuat',
            'data' => $bill->load(['retributionType', 'opd', 'taxObject'])
        ], 201);
    }

    /**
     * Bulk generate bills for a retribution type based on tax objects
     */
    public function bulkStore(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'retribution_type_id' => 'required|exists:retribution_types,id',
            'period' => 'required|string',
            'due_date' => 'required|date',
        ]);

        $type = RetributionType::find($request->retribution_type_id);

        if (!$user->isSuperAdmin() && $type->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get all active tax objects for this type
        $objects = TaxObject::where('retribution_type_id', $type->id)
            ->where('status', 'active')
            ->get();

        $createdCount = 0;
        foreach ($objects as $obj) {
            Bill::create([
                'user_id' => $user->id,
                'taxpayer_id' => $obj->taxpayer_id,
                'tax_object_id' => $obj->id,
                'opd_id' => $type->opd_id,
                'retribution_type_id' => $type->id,
                'bill_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'amount' => $type->base_amount, 
                'status' => 'pending',
                'period' => $request->period,
                'due_date' => $request->due_date,
            ]);
            $createdCount++;
        }

        return response()->json([
            'message' => "Berhasil generate {$createdCount} tagihan",
            'count' => $createdCount
        ]);
    }

    /**
     * Show bill details
     */
    public function show(Request $request, Bill $bill)
    {
        $user = $request->user();
        
        if (!$user->isSuperAdmin() && $bill->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $bill->load(['retributionType', 'opd', 'payments', 'taxObject', 'taxpayer'])
        ]);
    }

    /**
     * List bills for a citizen (by NIK)
     */
    public function citizenBills(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
        ]);

        $bills = Bill::with(['retributionType', 'opd', 'taxObject'])
            ->whereHas('taxpayer', function($q) use ($request) {
                $q->where('nik', $request->nik);
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $bills
        ]);
    }
}
