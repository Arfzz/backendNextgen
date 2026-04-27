<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Repositories\PackageRepository;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    public function __construct(private readonly PackageRepository $packageRepo) {}

    public function index(): JsonResponse
    {
        return response()->json(PackageResource::collection($this->packageRepo->all()));
    }

    public function show(string $id): JsonResponse
    {
        $package = $this->packageRepo->findById($id);

        if (! $package) {
            return response()->json(['message' => 'Package not found.'], 404);
        }

        return response()->json(new PackageResource($package));
    }
}
