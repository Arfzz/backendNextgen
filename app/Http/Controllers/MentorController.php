<?php

namespace App\Http\Controllers;

use App\Models\Mentor;
use Illuminate\Http\Request;

class MentorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Mentor::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_mentor', 'regexp', '/' . preg_quote($request->search) . '/i');
        }

        $mentors = $query->get();
        $search = $request->search;

        return view('mentor.index', compact('mentors', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $paketBeasiswas = \App\Models\PaketBeasiswa::all();
        return view('mentor.create', compact('paketBeasiswas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mentor' => 'required|string|max:255',
            'pendidikan'  => 'required|string|max:255',
            'awardee'     => 'required|string|max:255',
            'rating'      => 'required|numeric|min:0|max:5',
        ]);

        Mentor::create($validated);

        return redirect()->route('mentor.index')->with('success', 'Data Mentor berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $mentor = Mentor::findOrFail($id);
        return view('mentor.show', compact('mentor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $mentor = Mentor::findOrFail($id);
        $paketBeasiswas = \App\Models\PaketBeasiswa::all();
        return view('mentor.edit', compact('mentor', 'paketBeasiswas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $mentor = Mentor::findOrFail($id);

        $validated = $request->validate([
            'nama_mentor' => 'required|string|max:255',
            'pendidikan'  => 'required|string|max:255',
            'awardee'     => 'required|string|max:255',
            'rating'      => 'required|numeric|min:0|max:5',
        ]);

        $mentor->update($validated);

        return redirect()->route('mentor.index')->with('success', 'Data Mentor berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $mentor = Mentor::findOrFail($id);
        $mentor->delete();

        return redirect()->route('mentor.index')->with('success', 'Data Mentor berhasil dihapus!');
    }
}
