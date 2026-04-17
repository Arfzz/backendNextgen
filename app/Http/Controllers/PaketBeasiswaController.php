<?php

namespace App\Http\Controllers;

use App\Models\PaketBeasiswa;
use Illuminate\Http\Request;

class PaketBeasiswaController extends Controller
{
    /**
     * Display a listing of paket beasiswa.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = PaketBeasiswa::query();

        if ($search) {
            $query->where('nama_beasiswa', 'like', '%' . $search . '%');
        }

        $paketBeasiswas = $query->orderBy('created_at', 'desc')->get();

        return view('paket-beasiswa.index', compact('paketBeasiswas', 'search'));
    }

    /**
     * Show the form for creating a new paket beasiswa.
     */
    public function create()
    {
        return view('paket-beasiswa.create');
    }

    /**
     * Store a newly created paket beasiswa.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_beasiswa' => 'required|string|max:255',
            'fase_checkpoint' => 'required|array|min:1',
            'fase_checkpoint.*' => 'required|string',
            'persyaratan' => 'required|array|min:1',
            'persyaratan.*' => 'required|string',
            'deadline' => 'required|date',
            'harga' => 'required|integer|min:0',
        ]);

        PaketBeasiswa::create($validated);

        return redirect()->route('paket-beasiswa.index')
            ->with('success', 'Paket Beasiswa berhasil ditambahkan!');
    }

    /**
     * Display the specified paket beasiswa.
     */
    public function show(string $id)
    {
        $paketBeasiswa = PaketBeasiswa::findOrFail($id);
        return view('paket-beasiswa.show', compact('paketBeasiswa'));
    }

    /**
     * Show the form for editing the specified paket beasiswa.
     */
    public function edit(string $id)
    {
        $paketBeasiswa = PaketBeasiswa::findOrFail($id);
        return view('paket-beasiswa.edit', compact('paketBeasiswa'));
    }

    /**
     * Update the specified paket beasiswa.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_beasiswa' => 'required|string|max:255',
            'fase_checkpoint' => 'required|array|min:1',
            'fase_checkpoint.*' => 'required|string',
            'persyaratan' => 'required|array|min:1',
            'persyaratan.*' => 'required|string',
            'deadline' => 'required|date',
            'harga' => 'required|integer|min:0',
        ]);

        $paketBeasiswa = PaketBeasiswa::findOrFail($id);
        $paketBeasiswa->update($validated);

        return redirect()->route('paket-beasiswa.index')
            ->with('success', 'Paket Beasiswa berhasil diperbarui!');
    }

    /**
     * Remove the specified paket beasiswa.
     */
    public function destroy(string $id)
    {
        $paketBeasiswa = PaketBeasiswa::findOrFail($id);
        $paketBeasiswa->delete();

        return redirect()->route('paket-beasiswa.index')
            ->with('success', 'Paket Beasiswa berhasil dihapus!');
    }
}
