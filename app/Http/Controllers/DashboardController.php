<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Taxpayer;
use App\Models\Opd;
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
        $opdId = $user->role === 'opd' ? $user->opd_id : null;

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
            ]
        ]);
    }

    /**
     * Get revenue trend data for the chart
     */
    public function getRevenueTrend(Request $request)
    {
        $user = $request->user();
        $opdId = $user->role === 'opd' ? $user->opd_id : null;

        $query = Payment::select(
            DB::raw('MONTHNAME(paid_at) as month'),
            DB::raw('SUM(amount) as amount')
        )
        ->groupBy('month')
        ->orderBy(DB::raw('MONTH(paid_at)'));

        if ($opdId) {
            $query->whereExists(function ($q) use ($opdId) {
                $q->select(DB::raw(1))
                    ->from('bills')
                    ->whereColumn('bills.id', 'payments.bill_id')
                    ->where('bills.opd_id', $opdId);
            });
        }

        // Limit to last 6 months for chart readability
        $trend = $query->limit(6)->get();

        return response()->json($trend);
    }

    /**
     * Get geo-potential data for the map
     */
    public function getMapPotentials(Request $request)
    {
        $user = $request->user();
        $opdId = $user->role === 'opd' ? $user->opd_id : null;

        // In a real scenario, Taxpayers or Objects should have lat/long.
        // For now, we return mock positions based on existing OPDs/Taxpayers
        // to match the Dashboard.tsx Leaflet map requirements.
        $potentials = [
            ['position' => [-5.47, 122.6], 'name' => 'Retribusi Parkir', 'agency' => 'Dishub'],
            ['position' => [-5.48, 122.61], 'name' => 'Retribusi Pelayanan Pasar', 'agency' => 'Disperindag'],
            ['position' => [-5.46, 122.59], 'name' => 'Retribusi IMB/PBG', 'agency' => 'DPMPTSP'],
            ['position' => [-5.475, 122.605], 'name' => 'Retribusi Tempat Rekreasi', 'agency' => 'Disparekraf'],
        ];

        return response()->json($potentials);
    }
}
