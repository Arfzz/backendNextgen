<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardChartController extends Controller
{
    public function mentorVsPeserta()
    {
        return response()->json([
            'labels' => ['Mentor', 'Peserta'],
            'data' => [30, 15],
            'backgroundColor' => ['#02BBE5', '#FFD362']
        ]);
    }

    public function topBeasiswa(Request $request)
    {
        $limit = $request->query('limit', 3);
        
        $allData = [
            ['label' => 'Beasiswa Unggulan', 'value' => 17],
            ['label' => 'Tanoto Scholarship', 'value' => 13],
            ['label' => 'BSI Scholarship Unggulan', 'value' => 10],
            ['label' => 'Djarum Beasiswa Plus', 'value' => 8],
            ['label' => 'Beasiswa LPDP', 'value' => 6],
            ['label' => 'Bakti BCA', 'value' => 5],
            ['label' => 'Karya Salemba Empat', 'value' => 4],
        ];

        $filteredData = array_slice($allData, 0, $limit);

        return response()->json([
            'labels' => array_column($filteredData, 'label'),
            'data' => array_column($filteredData, 'value'),
            'backgroundColor' => '#02BBE5'
        ]);
    }

    public function totalPenjualan(Request $request)
    {
        $filter = $request->query('filter', '2026'); // default 2026

        // Dummy data mock based on filter
        if ($filter === '2025') {
            $data = [42, 55, 40, 38, 50, 45, 60, 45, 52, 65, 70, 55];
        } else if ($filter === 'q1') {
            return response()->json([
                'labels' => ['Jan', 'Feb', 'Mar'],
                'data' => [32, 45, 60],
                'borderColor' => '#8979FF',
                'backgroundColor' => 'rgba(137, 121, 255, 0.2)'
            ]);
        } else {
            $data = [32, 45, 60, 48, 70, 65, 80, 55, 62, 85, 90, 75];
        }

        return response()->json([
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'data' => $data,
            'borderColor' => '#8979FF',
            'backgroundColor' => 'rgba(137, 121, 255, 0.2)'
        ]);
    }

    public function totalPendapatan(Request $request)
    {
        $filter = $request->query('filter', 'yearly');

        if ($filter === 'monthly') {
            return response()->json([
                'labels' => ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                'datasets' => [
                    [
                        'label' => 'Bulan Ini',
                        'data' => [150000, 200000, 180000, 240000],
                        'borderColor' => '#02BBE5',
                        'backgroundColor' => 'rgba(2, 187, 229, 0.3)'
                    ],
                    [
                        'label' => 'Bulan Lalu',
                        'data' => [120000, 160000, 150000, 190000],
                        'borderColor' => '#FFD362',
                        'backgroundColor' => 'rgba(255, 211, 98, 0.3)'
                    ]
                ]
            ]);
        }

        // Default Yearly Comparison (2026 vs 2025)
        return response()->json([
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'datasets' => [
                [
                    'label' => 'Tahun 2026',
                    'data' => [200000, 350000, 420000, 380000, 500000, 450000, 600000, 490000, 520000, 680000, 720000, 650000],
                    'borderColor' => '#8979FF',
                    'backgroundColor' => 'rgba(137, 121, 255, 0.4)'
                ],
                [
                    'label' => 'Tahun 2025',
                    'data' => [150000, 280000, 320000, 300000, 400000, 380000, 500000, 420000, 480000, 560000, 600000, 550000],
                    'borderColor' => '#02BBE5',
                    'backgroundColor' => 'rgba(2, 187, 229, 0.4)'
                ]
            ]
        ]);
    }
}

