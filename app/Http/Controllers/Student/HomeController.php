<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\MentorResource;
use App\Http\Resources\PackageResource;
use App\Http\Resources\UserResource;
use App\Services\StudentDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private readonly StudentDashboardService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->home($request->user());

        return response()->json([
            'user'                => new UserResource($data['user']),
            'global_progress'     => $data['global_progress'],
            'upcoming_activities' => $data['upcoming_activities'],
            'articles'            => ArticleResource::collection($data['articles']),
            'packages'            => PackageResource::collection($data['packages']),
            'mentors'             => MentorResource::collection($data['mentors']),
        ]);
    }
}
