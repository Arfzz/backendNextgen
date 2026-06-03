<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => (string) $this->_id,
            'name'            => $this->name,
            'email'           => $this->email,
            'role'            => $this->role?->value ?? $this->role,
            'university'      => $this->university,
            'profile_picture' => self::resolveUrl($this->profile_picture),
            'rating_score'    => $this->rating_score,
            'students_passed' => $this->students_passed,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Build a full public URL from a stored path.
     * Handles: null, already-full URLs, /storage/... paths, bare filenames.
     */
    public static function resolveUrl(?string $path): ?string
    {
        if (blank($path)) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        // Strip leading /storage/ or storage/ if present
        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, strlen('storage/'));
        }
        return url('storage/' . $clean);
    }
}
