<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardChartController extends Controller
{
    public function mentorVsPeserta()
    {
        $mentorCount = \App\Models\Mentor::count();
        $pesertaCount = \App\Models\User::where('role', 'student')->count();

        return response()->json([
            'labels' => ['Mentor', 'Peserta'],
            'data' => [$mentorCount, $pesertaCount],
            'backgroundColor' => ['#02BBE5', '#FFD362']
        ]);
    }

    public function topBeasiswa(Request $request)
    {
        $limit = $request->query('limit', 3);
        
        $orders = \App\Models\Order::where('status', 'paid')->get();
        $grouped = $orders->groupBy('package_name')->map(function ($group) {
            return $group->count();
        })->sortDesc()->take($limit);

        $labels = [];
        $data = [];
        foreach ($grouped as $name => $count) {
            $labels[] = $name ?: 'Tanpa Paket';
            $data[] = $count;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'backgroundColor' => '#02BBE5'
        ]);
    }

    public function totalPenjualan(Request $request)
    {
        $filter = $request->query('filter', date('Y'));

        $year = $filter === 'q1' ? date('Y') : (int)$filter;
        $start = \Carbon\Carbon::create($year)->startOfYear();
        $end = \Carbon\Carbon::create($year)->endOfYear();

        $orders = \App\Models\Order::where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($filter === 'q1') {
            $data = [0, 0, 0];
            foreach ($orders as $order) {
                $month = $order->created_at->month;
                if ($month <= 3) {
                    $data[$month - 1]++;
                }
            }
            return response()->json([
                'labels' => ['Jan', 'Feb', 'Mar'],
                'data' => $data,
                'borderColor' => '#8979FF',
                'backgroundColor' => 'rgba(137, 121, 255, 0.2)'
            ]);
        }

        $data = array_fill(0, 12, 0);
        foreach ($orders as $order) {
            $month = $order->created_at->month;
            if ($month >= 1 && $month <= 12) {
                $data[$month - 1]++;
            }
        }

        return response()->json([
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'data' => $data,
            'borderColor' => '#8979FF',
            'backgroundColor' => 'rgba(137, 121, 255, 0.2)'
        ]);
    }

    public function statusTransaksi()
    {
        $orders = \App\Models\Order::all();
        $paid = $orders->where('status', 'paid')->count();
        $pending = $orders->where('status', 'pending')->count();
        $failed = $orders->whereIn('status', ['failed', 'expired', 'cancelled'])->count();

        return response()->json([
            'labels' => ['Berhasil', 'Menunggu', 'Gagal/Batal'],
            'data' => [$paid, $pending, $failed],
            'backgroundColor' => ['#10B981', '#F59E0B', '#EF4444']
        ]);
    }
}

