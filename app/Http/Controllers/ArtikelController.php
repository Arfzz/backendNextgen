<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Artikel::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('judul_artikel', 'regexp', '/' . preg_quote($request->search) . '/i');
        }

        $artikels = $query->get();
        $search = $request->search;

        return view('artikel.index', compact('artikels', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('artikel.create');
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

        Artikel::create($validated);

        return redirect()->route('artikel.index')->with('success', 'Data Artikel berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $artikel = Artikel::findOrFail($id);
        return view('artikel.show', compact('artikel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $artikel = Artikel::findOrFail($id);
        return view('artikel.edit', compact('artikel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $artikel = Artikel::findOrFail($id);

        $validated = $request->validate([
            'judul_artikel' => 'required|string|max:255',
            'url'           => 'required|url|max:255',
            'thumbnail'     => 'required|url|max:255',
        ]);

        $artikel->update($validated);

        return redirect()->route('artikel.index')->with('success', 'Data Artikel berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $artikel = Artikel::findOrFail($id);
        $artikel->delete();

        return redirect()->route('artikel.index')->with('success', 'Data Artikel berhasil dihapus!');
    }
}
