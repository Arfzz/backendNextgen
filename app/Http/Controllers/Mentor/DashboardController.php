<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClassMemberResource;
use App\Http\Resources\MentorResource;
use App\Http\Resources\UserResource;
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
            'mentor'              => new MentorResource($data['mentor']),
            'upcoming_activities' => $data['upcoming_activities'],
            'students'            => ClassMemberResource::collection($data['students']),
        ]);
    }
}
