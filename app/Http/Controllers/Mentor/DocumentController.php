<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
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

    public function update(Request $request, string $documentId): JsonResponse
    {
        $validated = $request->validate(['title' => 'required|string|max:255']);
        $doc = Document::find($documentId);
        if (! $doc) {
            return response()->json(['message' => 'Document not found.'], 404);
        }
        $doc->title = $validated['title'];
        $doc->save();
        return response()->json([
            'message'  => 'Document updated.',
            'document' => new DocumentResource($doc),
        ]);
    }

    public function destroy(string $documentId): JsonResponse
    {
        $deleted = $this->service->deleteDocument($documentId);
        if (!$deleted) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        return response()->json(['message' => 'Document deleted.']);
    }
}
