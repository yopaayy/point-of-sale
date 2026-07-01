<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penjualan;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    public function getChartData(Request $request)
    {
        $filter = $request->query('filter', '7_hari');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $labels = [];
        $data = [];

        if ($filter === 'hari_ini') {
            // Data per jam untuk hari ini (8 AM - 10 PM)
            for ($i = 8; $i <= 22; $i++) {
                $labels[] = sprintf('%02d:00', $i);
                $sum = Penjualan::whereDate('tanggal', Carbon::today())
                                ->whereTime('tanggal', '>=', sprintf('%02d:00:00', $i))
                                ->whereTime('tanggal', '<', sprintf('%02d:59:59', $i))
                                ->where('status', 'selesai')
                                ->sum('total_akhir');
                $data[] = $sum;
            }
        } elseif ($filter === '7_hari') {
            // 7 hari terakhir
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('d M');
                $data[] = Penjualan::whereDate('tanggal', $date)
                                   ->where('status', 'selesai')
                                   ->sum('total_akhir');
            }
        } elseif ($filter === '1_bulan') {
            // 1 bulan terakhir (misal 30 hari)
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('d M');
                $data[] = Penjualan::whereDate('tanggal', $date)
                                   ->where('status', 'selesai')
                                   ->sum('total_akhir');
            }
        } elseif ($filter === '1_tahun') {
            // 1 tahun terakhir (12 bulan)
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::today()->startOfMonth()->subMonths($i);
                $labels[] = $date->format('M Y');
                $data[] = Penjualan::whereYear('tanggal', $date->year)
                                   ->whereMonth('tanggal', $date->month)
                                   ->where('status', 'selesai')
                                   ->sum('total_akhir');
            }
        } elseif ($filter === 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $diffInDays = $start->diffInDays($end);

            if ($diffInDays <= 31) {
                // Per hari
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $labels[] = $date->format('d M');
                    $data[] = Penjualan::whereDate('tanggal', $date)
                                       ->where('status', 'selesai')
                                       ->sum('total_akhir');
                }
            } else {
                // Per bulan jika lebih dari 1 bulan
                $startMonth = $start->copy()->startOfMonth();
                $endMonth = $end->copy()->startOfMonth();
                for ($date = $startMonth->copy(); $date->lte($endMonth); $date->addMonth()) {
                    $labels[] = $date->format('M Y');
                    $data[] = Penjualan::whereYear('tanggal', $date->year)
                                       ->whereMonth('tanggal', $date->month)
                                       ->whereBetween('tanggal', [$start, $end])
                                       ->where('status', 'selesai')
                                       ->sum('total_akhir');
                }
            }
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }
}
