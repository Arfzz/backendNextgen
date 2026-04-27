<?php

namespace App\Repositories;

use App\Models\Package;
use Illuminate\Support\Collection;

class PackageRepository
{
    public function all(): Collection
    {
        return Package::orderBy('created_at', 'desc')->get();
    }

    public function findById(string $id): ?Package
    {
        return Package::find($id);
    }
}
