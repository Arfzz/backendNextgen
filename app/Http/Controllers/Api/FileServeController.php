<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves files from the public disk through PHP (bypasses symlink issues on Windows).
 *
 * GET /api/v1/files/serve?path=checkpoint_submissions/uuid.pdf
 */
class FileServeController extends Controller
{
    public function serve(Request $request): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $path = $request->query('path', '');

        if (empty($path)) {
            return response()->json(['message' => 'Path required.'], 400);
        }

        // Security: prevent path traversal
        $path = ltrim(str_replace(['..', '\\'], ['', '/'], $path), '/');

        if (! Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
        $filename  = basename($path);

        return Storage::disk('public')->response($path, $filename, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
