<?php

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Support\Collection;

class ArticleRepository
{
    public function latest(int $limit = 5): Collection
    {
        return Article::orderByDesc('published_at')->limit($limit)->get();
    }

    public function findById(string $id): ?Article
    {
        return Article::find($id);
    }
}
