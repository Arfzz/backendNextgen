<?php

namespace App\Http\Controllers;

use App\Models\PaketBeasiswa;
use App\Models\Mentor;
use App\Models\Artikel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view with real counts and mock chart data.
     */
    public function index()
    {
        // Fetch real data metrics
        $totalBeasiswa = PaketBeasiswa::count();
        $totalMentor = Mentor::count();
        $totalArtikel = Artikel::count();

        // Create Mock data for the 'Revenue Per Bulan' Chart
        $chartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'data'   => [5000, 10000, 7500, 15000, 20000, 18000, 25000, 22000, 30000, 28000, 35000, 45000]
        ];

        return view('dashboard.index', compact(
            'totalBeasiswa',
            'totalMentor',
            'totalArtikel',
            'chartData'
        ));
    }
}
