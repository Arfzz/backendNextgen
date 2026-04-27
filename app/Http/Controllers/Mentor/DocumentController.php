<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Services\MentorContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(private readonly MentorContentService $service) {}

    public function store(StoreDocumentRequest $request, string $classId): JsonResponse
    {
        $document = $this->service->uploadDocument(
            $classId,
            $request->user(),
            $request->validated(),
            $request->file('file')
        );

        return response()->json([
            'message'  => 'Document uploaded.',
            'document' => new DocumentResource($document),
        ], 201);
    }
}
