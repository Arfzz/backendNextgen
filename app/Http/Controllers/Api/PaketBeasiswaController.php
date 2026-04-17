<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaketBeasiswa;
use Illuminate\Http\Request;

class PaketBeasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paketBeasiswas = PaketBeasiswa::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar data Paket Beasiswa',
            'data'    => $paketBeasiswas
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_beasiswa'     => 'required|string|max:255',
            'fase_checkpoint'   => 'required|array|min:1',
            'fase_checkpoint.*' => 'required|string',
            'persyaratan'       => 'required|array|min:1',
            'persyaratan.*'     => 'required|string',
            'deadline'          => 'required|date',
            'harga'             => 'required|integer|min:0',
        ]);

        $paketBeasiswa = PaketBeasiswa::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Paket Beasiswa berhasil disimpan',
            'data'    => $paketBeasiswa
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $paketBeasiswa = PaketBeasiswa::find($id);

        if (!$paketBeasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail data Paket Beasiswa',
            'data'    => $paketBeasiswa
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $paketBeasiswa = PaketBeasiswa::find($id);

        if (!$paketBeasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'nama_beasiswa'     => 'required|string|max:255',
            'fase_checkpoint'   => 'required|array|min:1',
            'fase_checkpoint.*' => 'required|string',
            'persyaratan'       => 'required|array|min:1',
            'persyaratan.*'     => 'required|string',
            'deadline'          => 'required|date',
            'harga'             => 'required|numeric|min:0',
        ]);

        $paketBeasiswa->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data Paket Beasiswa berhasil diubah',
            'data'    => $paketBeasiswa
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $paketBeasiswa = PaketBeasiswa::find($id);

        if (!$paketBeasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $paketBeasiswa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Paket Beasiswa berhasil dihapus'
        ], 200);
    }
}
