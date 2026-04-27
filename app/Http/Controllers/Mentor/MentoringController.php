<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\StoreMentoringRequest;
use App\Http\Resources\MentoringSessionResource;
use App\Services\MentorContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentoringController extends Controller
{
    public function __construct(private readonly MentorContentService $service) {}

    public function store(StoreMentoringRequest $request, string $classId): JsonResponse
    {
        $session = $this->service->createMentoringSession($classId, $request->validated());

        return response()->json([
            'message' => 'Mentoring session created.',
            'session' => new MentoringSessionResource($session),
        ], 201);
    }
}
