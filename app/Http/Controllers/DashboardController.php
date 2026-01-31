<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Taxpayer;
use App\Models\Opd;
use App\Models\TaxObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get KPI statistics for the dashboard
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        $opdId = !$user->isSuperAdmin() ? $user->opd_id : null;

        $totalRevenueQuery = Payment::query();
        $pendingBillsQuery = Bill::where('status', 'pending');
        $activeTaxpayersQuery = Taxpayer::where('is_active', true);

        if ($opdId) {
            $totalRevenueQuery->whereExists(function ($query) use ($opdId) {
                $query->select(DB::raw(1))
                    ->from('bills')
                    ->whereColumn('bills.id', 'payments.bill_id')
                    ->where('bills.opd_id', $opdId);
            });
            $pendingBillsQuery->where('opd_id', $opdId);
            $activeTaxpayersQuery->where('opd_id', $opdId);
        }

        $totalRevenue = $totalRevenueQuery->sum('amount');
        $pendingBillsCount = $pendingBillsQuery->count();
        $activeTaxpayersCount = $activeTaxpayersQuery->count();

        // Calculate collection rate (Paid Bills / Total Bills)
        $totalBillsCountQuery = Bill::query();
        $paidBillsCountQuery = Bill::where('status', 'paid');

        if ($opdId) {
            $totalBillsCountQuery->where('opd_id', $opdId);
            $paidBillsCountQuery->where('opd_id', $opdId);
        }

        $totalBills = $totalBillsCountQuery->count();
        $paidBills = $paidBillsCountQuery->count();
        $collectionRate = $totalBills > 0 ? round(($paidBills / $totalBills) * 100, 1) : 0;

        return response()->json([
            'total_revenue' => $totalRevenue,
            'collection_rate' => $collectionRate,
            'pending_bills' => $pendingBillsCount,
            'active_taxpayers' => $activeTaxpayersCount,
            // Temporary growth values until we have historical data
            'trends' => [
                'revenue' => '+12.5%',
                'collection_rate' => '+5.2%',
                'pending_bills' => '-8.1%',
                'active_taxpayers' => '+15.3%'
            ],
            'revenue_by_type' => Payment::join('bills', 'payments.bill_id', '=', 'bills.id')
                ->join('retribution_types', 'bills.retribution_type_id', '=', 'retribution_types.id')
                ->where('bills.opd_id', $opdId ?: DB::raw('bills.opd_id'))
                ->select('retribution_types.name', DB::raw('SUM(payments.amount) as total'))
                ->groupBy('retribution_types.name')
                ->get()
        ]);
    }

    /**
     * Get revenue trend data for the chart
     */
    public function getRevenueTrend(Request $request)
    {
        $user = $request->user();
        $opdId = !$user->isSuperAdmin() ? $user->opd_id : null;

        $trend = Payment::select(
            DB::raw('YEAR(paid_at) as year'),
            DB::raw('MONTH(paid_at) as month_num'),
            DB::raw('MONTHNAME(paid_at) as month'),
            DB::raw('SUM(amount) as amount')
        )
        ->when($opdId, function ($q) use ($opdId) {
            $q->whereExists(function ($sub) use ($opdId) {
                $sub->select(DB::raw(1))
                    ->from('bills')
                    ->whereColumn('bills.id', 'payments.bill_id')
                    ->where('bills.opd_id', $opdId);
            });
        })
        ->groupBy('year', 'month_num', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month_num', 'desc')
        ->limit(6)
        ->get()
        ->reverse()
        ->values();

        return response()->json($trend);
    }

    /**
     * Get geo-potential data for the map
     */
    public function getMapPotentials(Request $request)
    {
        $user = $request->user();
        $opdId = !$user->isSuperAdmin() ? $user->opd_id : null;
        
        $query = TaxObject::with(['opd', 'retributionType'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($opdId) {
            $query->where('opd_id', $opdId);
        }

        $potentials = $query->get()->map(function($obj) {
            return [
                'position' => [(float)$obj->latitude, (float)$obj->longitude],
                'name' => $obj->name . ' (' . ($obj->retributionType->name ?? 'N/A') . ')',
                'agency' => $obj->opd->name ?? 'N/A',
                'address' => $obj->address,
                'status' => $obj->status,
            ];
        });

        return response()->json($potentials);
    }
}
