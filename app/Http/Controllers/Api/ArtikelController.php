<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $artikels = Artikel::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar data Artikel',
            'data'    => $artikels
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_artikel' => 'required|string|max:255',
            'url'           => 'required|url|max:255',
            'thumbnail'     => 'required|url|max:255',
        ]);

        $artikel = Artikel::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Artikel berhasil disimpan',
            'data'    => $artikel
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $artikel = Artikel::find($id);

        if (!$artikel) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail data Artikel',
            'data'    => $artikel
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $artikel = Artikel::find($id);

        if (!$artikel) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'judul_artikel' => 'required|string|max:255',
            'url'           => 'required|url|max:255',
            'thumbnail'     => 'required|url|max:255',
        ]);

        $artikel->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Artikel berhasil diubah',
            'data'    => $artikel
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $artikel = Artikel::find($id);

        if (!$artikel) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $artikel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Artikel berhasil dihapus'
        ], 200);
    }
}
