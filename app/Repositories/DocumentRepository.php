<?php

namespace App\Repositories;

use App\Models\Document;
use Illuminate\Support\Collection;

class DocumentRepository
{
    public function findById(string $id): ?Document
    {
        return Document::find($id);
    }

    public function findByClassId(string $classId): Collection
    {
        return Document::where('class_id', $classId)
            ->orderByDesc('uploaded_at')
            ->get();
    }

    public function create(array $data): Document
    {
        return Document::create($data);
    }

    public function delete(Document $document): bool
    {
        return $document->delete();
    }
}
