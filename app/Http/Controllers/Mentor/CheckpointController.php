<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\StoreCheckpointRequest;
use App\Http\Resources\CheckpointResource;
use App\Services\MentorContentService;
use Illuminate\Http\JsonResponse;

class CheckpointController extends Controller
{
    public function __construct(private readonly MentorContentService $service) {}

    public function store(StoreCheckpointRequest $request, string $classId): JsonResponse
    {
        $checkpoint = $this->service->createCheckpoint($classId, $request->validated());

        return response()->json([
            'message'    => 'Checkpoint created.',
            'checkpoint' => new CheckpointResource($checkpoint),
        ], 201);
    }
}
