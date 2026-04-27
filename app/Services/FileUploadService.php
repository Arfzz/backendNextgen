<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Store an uploaded file and return its fully-qualified public URL.
     * Uses Laravel Storage abstraction — easy to swap to S3/Minio later.
     *
     * @param  UploadedFile  $file
     * @param  string        $folder  e.g. 'submissions', 'documents'
     * @return string        Full URL (e.g. http://localhost/storage/submissions/uuid.pdf)
     */
    public function upload(UploadedFile $file, string $folder = 'uploads'): string
    {
        $filename  = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path      = $file->storeAs($folder, $filename, 'public');

        return Storage::disk('public')->url($path);
    }
}
