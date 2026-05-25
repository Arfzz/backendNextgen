<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Resources\MentorResource;
use App\Services\MentorDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly MentorDashboardService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->dashboard($request->user());

        return response()->json([
            'mentor_profile'      => new MentorResource($data['mentor_profile']),
            'upcoming_activities' => $data['upcoming_activities'],
            'students'            => $data['students'], // already plain array from service
        ]);
    }
}

