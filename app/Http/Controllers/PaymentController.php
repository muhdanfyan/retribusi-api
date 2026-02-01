<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Record a payment for a specific bill
     */
    public function store(Request $request, Bill $bill)
    {
        $user = $request->user();

        // Authority check: only petugas or opd (of same opd_id) can record payments
        if (!$user->isSuperAdmin()) {
            if ($user->role !== 'opd' && $user->role !== 'petugas') {
                return response()->json(['message' => 'Role tidak memiliki wewenang mencatat pembayaran'], 403);
            }
            if ($bill->opd_id !== $user->opd_id) {
                return response()->json(['message' => 'Unauthorized OPD'], 403);
            }
        }

        if ($bill->status === 'lunas') {
            return response()->json(['message' => 'Tagihan sudah lunas'], 422);
        }

        $request->validate([
            'payment_method' => 'required|string|in:cash,qris,va',
            'amount' => 'required|numeric|min:0',
            'proof_url' => 'nullable|string',
        ]);

        // In a real scenario, we might want to check if amount matches bill amount
        // but for now we follow the user's request for flexibility in field payment.

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'transaction_id' => 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'paid_at' => Carbon::now(),
            'approved_by' => $user->id, // Mark as approved by the person recording it (Admin/Petugas)
            'proof_url' => $request->proof_url,
        ]);

        $bill->update([
            'status' => 'lunas'
        ]);

        return response()->json([
            'message' => 'Pembayaran berhasil dicatat',
            'data' => $payment->load('bill')
        ], 201);
    }
}
