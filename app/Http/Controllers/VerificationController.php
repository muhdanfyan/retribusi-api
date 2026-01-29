<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VerificationController extends Controller
{
    /**
     * List verifications (OPD-scoped)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Verification::with(['opd', 'submitter', 'verifier']);

        if ($user->role === 'opd' && $user->opd_id) {
            $query->where('opd_id', $user->opd_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('taxpayer_name', 'like', "%{$search}%");
            });
        }

        $verifications = $query->latest('submitted_at')->paginate($request->get('per_page', 15));

        return response()->json($verifications);
    }

    /**
     * Update verification status (Approve/Reject)
     */
    public function updateStatus(Request $request, Verification $verification)
    {
        $user = $request->user();

        // Authority check
        if ($user->role === 'opd' && $verification->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected,in_review',
            'notes' => 'nullable|string',
        ]);

        $verification->update([
            'status' => $request->status,
            'notes' => $request->notes,
            'verifier_id' => $user->id,
            'verified_at' => in_array($request->status, ['approved', 'rejected']) ? Carbon::now() : null,
        ]);

        return response()->json([
            'message' => "Dokumen berhasil di-{$request->status}",
            'data' => $verification->load(['opd', 'submitter', 'verifier'])
        ]);
    }

    /**
     * Show verification details
     */
    public function show(Request $request, Verification $verification)
    {
        $user = $request->user();
        
        if ($user->role === 'opd' && $verification->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $verification->load(['opd', 'submitter', 'verifier'])
        ]);
    }
}
