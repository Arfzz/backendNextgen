<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaketBeasiswa;
use App\Models\Mentor;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PortfolioController extends Controller
{
    /**
     * Get statistics for the portfolio view.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $totalBeasiswa = PaketBeasiswa::count();
        $totalMentor = Mentor::count();
        $totalPeserta = User::where('role', 'student')->count();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio statistics retrieved successfully',
            'data' => [
                'total_beasiswa' => $totalBeasiswa,
                'total_mentor' => $totalMentor,
                'total_peserta' => $totalPeserta,
            ]
        ], 200);
    }
}
