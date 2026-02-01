<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get summary reports based on date range
     */
    public function getSummary(Request $request)
    {
        $user = $request->user();
        $opdId = !$user->isSuperAdmin() ? $user->opd_id : $request->query('opd_id');

        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Revenue by Type
        $revenueByType = Payment::join('bills', 'payments.bill_id', '=', 'bills.id')
            ->join('retribution_types', 'bills.retribution_type_id', '=', 'retribution_types.id')
            ->when($opdId, fn($q) => $q->where('bills.opd_id', $opdId))
            ->whereBetween('payments.paid_at', [$start, $end])
            ->select(
                'retribution_types.name as type',
                DB::raw('SUM(payments.amount) as amount'),
                DB::raw('COUNT(payments.id) as count')
            )
            ->groupBy('retribution_types.name')
            ->get();

        // Calculate percentages
        $totalAmount = $revenueByType->sum('amount');
        $revenueByType = $revenueByType->map(function ($item) use ($totalAmount) {
            $item->percentage = $totalAmount > 0 ? round(($item->amount / $totalAmount) * 100, 1) : 0;
            // Target is a placeholder for now, can be linked to a targets table later
            $item->target = $item->amount * 1.2; 
            return $item;
        });

        return response()->json([
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'total_revenue' => $totalAmount,
            'revenue_by_type' => $revenueByType,
            'stats' => [
                'total_transactions' => $revenueByType->sum('count'),
                'avg_transaction' => $revenueByType->sum('count') > 0 ? round($totalAmount / $revenueByType->sum('count'), 0) : 0
            ]
        ]);
    }

    /**
     * Get recent payments list
     */
    public function getRecent(Request $request)
    {
        $user = $request->user();
        $opdId = !$user->isSuperAdmin() ? $user->opd_id : $request->query('opd_id');

        $payments = Payment::with(['bill.retributionType', 'bill.taxpayer'])
            ->whereHas('bill', function($q) use ($opdId) {
                if ($opdId) $q->where('opd_id', $opdId);
            })
            ->orderBy('paid_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'taxpayer_name' => $p->bill->taxpayer->name ?? 'N/A',
                    'type' => $p->bill->retributionType->name ?? 'N/A',
                    'amount' => $p->amount,
                    'date' => $p->paid_at->toDateTimeString(),
                    'method' => $p->payment_method ?? 'CASH',
                    'status' => 'Verified' // Since it's already in payments table
                ];
            });

        return response()->json($payments);
    }
}
