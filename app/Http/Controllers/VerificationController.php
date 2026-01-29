<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VerificationController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * Store a new verification request with proof file
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'opd_id' => 'required|exists:opds,id',
            'taxpayer_name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'proof_file' => 'required|file|image|max:5120', // Max 5MB image
        ]);

        $proofFileUrl = $this->cloudinary->upload($request->file('proof_file'), 'verifications');

        $verification = Verification::create([
            'opd_id' => $request->opd_id,
            'user_id' => $user->id,
            'document_number' => 'VRC-' . strtoupper(uniqid()),
            'taxpayer_name' => $request->taxpayer_name,
            'type' => $request->type,
            'amount' => $request->amount,
            'proof_file_url' => $proofFileUrl,
            'status' => 'pending',
            'submitted_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Permintaan verifikasi berhasil dikirim',
            'data' => $verification->load(['opd', 'submitter'])
        ], 210);
    }
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
