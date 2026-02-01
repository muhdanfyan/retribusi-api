<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Taxpayer;
use App\Models\Opd;
use App\Models\TaxObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get KPI statistics for the dashboard with date filtering
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        $opdId = !$user->isSuperAdmin() ? $user->opd_id : null;

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Defaults to current month if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Previous period for trend calculation
        $diff = $start->diffInDays($end) + 1;
        $prevEnd = $start->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($diff - 1);

        // Revenue query
        $totalRevenue = $this->getRevenue($opdId, $start, $end);
        $prevRevenue = $this->getRevenue($opdId, $prevStart, $prevEnd);
        $revenueTrend = $this->calculateTrend($totalRevenue, $prevRevenue);

        // Pending Bills
        $pendingBillsCount = $this->getPendingCount($opdId, $start, $end);
        $prevPendingCount = $this->getPendingCount($opdId, $prevStart, $prevEnd);
        $pendingTrend = $this->calculateTrend($pendingBillsCount, $prevPendingCount, true); // Lower is better

        // Collection Rate
        $collectionRate = $this->getCollectionRate($opdId, $start, $end);
        $prevCollectionRate = $this->getCollectionRate($opdId, $prevStart, $prevEnd);
        $rateTrend = $this->calculateTrend($collectionRate, $prevCollectionRate);

        // Active Taxpayers (Usually total active, not necessarily by range, but let's stick to total for consistency with existing)
        $activeTaxpayersCount = Taxpayer::when($opdId, fn($q) => $q->where('opd_id', $opdId))
            ->where('is_active', true)
            ->count();
        
        $prevTaxpayersCount = Taxpayer::when($opdId, fn($q) => $q->where('opd_id', $opdId))
            ->where('is_active', true)
            ->where('created_at', '<', $start)
            ->count();
        $taxpayerTrend = $this->calculateTrend($activeTaxpayersCount, $prevTaxpayersCount);

        return response()->json([
            'total_revenue' => $totalRevenue,
            'collection_rate' => $collectionRate,
            'pending_bills' => $pendingBillsCount,
            'active_taxpayers' => $activeTaxpayersCount,
            'trends' => [
                'revenue' => ($revenueTrend >= 0 ? '+' : '') . $revenueTrend . '%',
                'collection_rate' => ($rateTrend >= 0 ? '+' : '') . $rateTrend . '%',
                'pending_bills' => ($pendingTrend >= 0 ? '+' : '') . $pendingTrend . '%',
                'active_taxpayers' => ($taxpayerTrend >= 0 ? '+' : '') . $taxpayerTrend . '%'
            ],
            'revenue_by_type' => Payment::join('bills', 'payments.bill_id', '=', 'bills.id')
                ->join('retribution_types', 'bills.retribution_type_id', '=', 'retribution_types.id')
                ->when($opdId, fn($q) => $q->where('bills.opd_id', $opdId))
                ->whereBetween('payments.paid_at', [$start->startOfDay(), $end->endOfDay()])
                ->select('retribution_types.name', DB::raw('SUM(payments.amount) as total'))
                ->groupBy('retribution_types.name')
                ->get()
        ]);
    }

    private function getRevenue($opdId, $start, $end)
    {
        return Payment::query()
            ->when($opdId, function ($query) use ($opdId) {
                $query->whereExists(function ($sub) use ($opdId) {
                    $sub->select(DB::raw(1))
                        ->from('bills')
                        ->whereColumn('bills.id', 'payments.bill_id')
                        ->where('bills.opd_id', $opdId);
                });
            })
            ->whereBetween('paid_at', [$start->startOfDay(), $end->endOfDay()])
            ->sum('amount');
    }

    private function getPendingCount($opdId, $start, $end)
    {
        return Bill::where('status', 'pending')
            ->when($opdId, fn($q) => $q->where('opd_id', $opdId))
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();
    }

    private function getCollectionRate($opdId, $start, $end)
    {
        $totalBills = Bill::when($opdId, fn($q) => $q->where('opd_id', $opdId))
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();
        
        $paidBills = Bill::where('status', 'paid')
            ->when($opdId, fn($q) => $q->where('opd_id', $opdId))
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();

        return $totalBills > 0 ? round(($paidBills / $totalBills) * 100, 1) : 0;
    }

    private function calculateTrend($current, $previous, $lowerIsBetter = false)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        $percentChange = round((($current - $previous) / $previous) * 100, 1);
        return $percentChange;
    }

    /**
     * Get revenue trend data for the chart
     * Supports dynamic date filtering and smart grouping
     */
    public function getRevenueTrend(Request $request)
    {
        $user = $request->user();
        $opdId = !$user->isSuperAdmin() ? $user->opd_id : null;

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Default to last 6 months if no dates provided
        if (!$startDate || !$endDate) {
            $start = Carbon::now()->subMonths(5)->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        } else {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        }

        $diffInDays = $start->diffInDays($end) + 1;

        // Dynamic grouping: If range <= 31 days, group by day; otherwise group by month
        if ($diffInDays <= 31) {
            // Daily grouping for short ranges (day/week/month view)
            $trend = Payment::select(
                DB::raw('DATE(paid_at) as date_label'),
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
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy('date_label')
            ->orderBy('date_label', 'asc')
            ->get()
            ->map(function ($item) {
                $date = Carbon::parse($item->date_label);
                return [
                    'month' => $date->format('d M'), // e.g., "15 Jan"
                    'amount' => $item->amount,
                ];
            });
        } else {
            // Monthly grouping for longer ranges (yearly view)
            $trend = Payment::select(
                DB::raw('YEAR(paid_at) as year'),
                DB::raw('MONTH(paid_at) as month_num'),
                DB::raw('MONTHNAME(paid_at) as month_name'),
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
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy('year', 'month_num', 'month_name')
            ->orderBy('year', 'asc')
            ->orderBy('month_num', 'asc')
            ->limit(12)
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month_name,
                    'amount' => $item->amount,
                ];
            });
        }

        return response()->json($trend);
    }

    /**
     * Get geo-potential data for the map
     */
    public function getMapPotentials(Request $request)
    {
        $user = $request->user();
        $opdId = !$user->isSuperAdmin() ? $user->opd_id : null;
        
        $query = \App\Models\Zone::with(['opd', 'retributionType'])
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
                'address' => $obj->description,
                'status' => 'active',
                'icon' => $obj->retributionType->icon ?? null,
                'retribution_type_id' => $obj->retribution_type_id,
            ];
        });

        return response()->json($potentials);
    }
}
