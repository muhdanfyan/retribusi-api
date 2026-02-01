<?php

namespace App\Http\Controllers;

use App\Models\TaxObject;
use Illuminate\Http\Request;

class TaxObjectController extends Controller
{
    /**
     * List tax objects (OPD-scoped)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = TaxObject::with(['taxpayer', 'retributionType', 'opd', 'classification']);

        if ($user && $user->role === 'opd') {
            $query->where('opd_id', $user->opd_id);
        } elseif ($user && $user->role === 'petugas') {
            $query->where('opd_id', $user->opd_id);
            
            $assignments = $user->assignments;
            if ($assignments) {
                $query->where(function($q) use ($assignments) {
                    foreach ($assignments as $assignment) {
                        $q->orWhere(function($sq) use ($assignment) {
                            $sq->where('retribution_type_id', $assignment->retribution_type_id);
                            if ($assignment->retribution_classification_id) {
                                $sq->where('retribution_classification_id', $assignment->retribution_classification_id);
                            }
                        });
                    }
                });
            }
        }

        if ($request->has('retribution_type_id')) {
            $query->where('retribution_type_id', $request->retribution_type_id);
        }

        if ($request->has('taxpayer_id')) {
            $query->where('taxpayer_id', $request->taxpayer_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nop', 'like', "%{$search}%")
                  ->orWhereHas('taxpayer', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $objects = $query->latest()->paginate($request->get('per_page', 50));

        return response()->json($objects);
    }

    /**
     * Show details
     */
    public function show(Request $request, TaxObject $taxObject)
    {
        $user = $request->user();
        if (!$user->isSuperAdmin() && $taxObject->opd_id !== $user->opd_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $taxObject->load(['taxpayer', 'retributionType', 'opd', 'classification'])
        ]);
    }
}
