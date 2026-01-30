<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Models\TaxObject;
use App\Models\Bill;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        $opdId = $request->opd_id;
        if (!$user->isSuperAdmin()) {
            $opdId = $user->opd_id;
        }

        $verification = Verification::create([
            'opd_id' => $opdId,
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
        ], 201);
    }

    /**
     * List verifications (OPD-scoped)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Verification::with(['opd', 'submitter', 'verifier', 'taxObject']);

        if (!$user->isSuperAdmin() && $user->opd_id) {
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
        if (!$user->isSuperAdmin() && $verification->opd_id !== $user->opd_id) {
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

        // If this is an object registration and it's approved, activate the object
        if ($request->status === 'approved' && $verification->tax_object_id) {
            $taxObject = TaxObject::with('retributionType')->find($verification->tax_object_id);
            if ($taxObject) {
                $taxObject->update([
                    'status' => 'active',
                    'approved_at' => Carbon::now(),
                ]);

                // Create initial bill automatically
                Bill::create([
                    'user_id' => $user->id,
                    'taxpayer_id' => $taxObject->taxpayer_id,
                    'tax_object_id' => $taxObject->id,
                    'opd_id' => $taxObject->opd_id,
                    'retribution_type_id' => $taxObject->retribution_type_id,
                    'bill_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                    'amount' => $taxObject->retributionType->base_amount,
                    'status' => 'pending',
                    'period' => Carbon::now()->isoFormat('MMMM YYYY'),
                    'due_date' => Carbon::now()->addDays(30),
                ]);
            }
        }

        if ($request->status === 'rejected' && $verification->tax_object_id) {
            $taxObject = TaxObject::find($verification->tax_object_id);
            if ($taxObject) {
                $taxObject->update(['status' => 'rejected']);
            }
        }

        return response()->json([
            'message' => "Dokumen berhasil di-{$request->status}",
            'data' => $verification->load(['opd', 'submitter', 'verifier', 'taxObject'])
        ]);
    }

    /**
     * Show verification details
     */
    public function show(Request $request, Verification $verification)
    {
        $user = $request->user();
        
        if (!$user->isSuperAdmin() && $verification->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $verification->load(['opd', 'submitter', 'verifier', 'taxObject', 'taxpayer'])
        ]);
    }
}
