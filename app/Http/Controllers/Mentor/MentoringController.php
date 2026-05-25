<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\StoreMentoringRequest;
use App\Http\Resources\MentoringSessionResource;
use App\Models\MentoringSession;
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

    public function update(Request $request, string $sessionId): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'session_date' => 'required|string',
            'link'         => 'nullable|string',
        ]);

        $session = $this->service->updateMentoringSession($sessionId, $validated);
        if (!$session) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        return response()->json([
            'message' => 'Mentoring session updated.',
            'session' => new MentoringSessionResource($session),
        ]);
    }

    public function destroy(string $sessionId): JsonResponse
    {
        $deleted = $this->service->deleteMentoringSession($sessionId);
        if (!$deleted) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        return response()->json(['message' => 'Mentoring session deleted.']);
    }
}
