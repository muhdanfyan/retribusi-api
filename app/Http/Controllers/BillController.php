<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Taxpayer;
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
        $query = Bill::with(['retributionType', 'user', 'opd']);

        if ($user->role === 'opd' && $user->opd_id) {
            $query->where('opd_id', $user->opd_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('bill_number', 'like', "%{$search}%");
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
            'taxpayer_id' => 'required|exists:taxpayers,id', // In real app, links to a user or taxpayer record
            'retribution_type_id' => 'required|exists:retribution_types,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|string',
            'due_date' => 'required|date',
            'metadata' => 'nullable|array',
        ]);

        $taxpayer = Taxpayer::find($request->taxpayer_id);
        
        // Security check
        if ($user->role === 'opd' && $taxpayer->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bill = Bill::create([
            'user_id' => $user->id, // Created by this admin
            'opd_id' => $taxpayer->opd_id,
            'retribution_type_id' => $request->retribution_type_id,
            'bill_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'amount' => $request->amount,
            'status' => 'pending',
            'period' => $request->period,
            'metadata' => $request->metadata,
            'due_date' => $request->due_date,
        ]);

        return response()->json([
            'message' => 'Tagihan berhasil dibuat',
            'data' => $bill->load(['retributionType', 'opd'])
        ], 201);
    }

    /**
     * Bulk generate bills for a retribution type
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

        if ($user->role === 'opd' && $type->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get all active taxpayers associated with this retribution type
        $taxpayers = Taxpayer::whereHas('retributionTypes', function($q) use ($request) {
            $q->where('retribution_types.id', $request->retribution_type_id);
        })->where('is_active', true)->get();

        $createdCount = 0;
        foreach ($taxpayers as $taxpayer) {
            Bill::create([
                'user_id' => $user->id,
                'opd_id' => $type->opd_id,
                'retribution_type_id' => $type->id,
                'bill_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'amount' => $type->base_amount, // Or custom amount from pivot if implemented
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
        
        if ($user->role === 'opd' && $bill->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $bill->load(['retributionType', 'opd', 'payments'])
        ]);
    }
}
