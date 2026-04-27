<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\CheckpointResource;
use App\Http\Resources\ClassMemberResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\MentoringSessionResource;
use App\Http\Resources\TaskResource;
use App\Services\StudentDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassDashboardController extends Controller
{
    public function __construct(private readonly StudentDashboardService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->classDashboard($request->user());

        if (! ($data['enrolled'] ?? false)) {
            return response()->json(['message' => 'You are not enrolled in any active class.'], 404);
        }

        return response()->json([
            'membership'         => new ClassMemberResource($data['membership']),
            'package_info'       => $data['package_info'],
            'checkpoints'        => CheckpointResource::collection($data['checkpoints']),
            'tasks_summary'      => $data['tasks_summary'],
            'tasks'              => TaskResource::collection($data['tasks']),
            'mentoring_sessions' => MentoringSessionResource::collection($data['mentoring_sessions']),
            'documents'          => DocumentResource::collection($data['documents']),
        ]);
    }
}
