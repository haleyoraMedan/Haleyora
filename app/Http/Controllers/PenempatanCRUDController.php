<?php

namespace App\Http\Controllers;

use App\Models\Penempatan;
use Illuminate\Http\Request;

class PenempatanCRUDController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('q', '');
        
        $query = Penempatan::query();
        if ($search) {
            $query->where('nama_kantor', 'like', "%{$search}%")
                  ->orWhere('kode_kantor', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
        }
        
        $penempatans = $query->paginate(15);
        
        return view('penempatan.index', compact('penempatans', 'search', 'user'));
    }

    public function create()
    {
        return view('penempatan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kantor' => 'required|unique:penempatans|max:50',
            'nama_kantor' => 'required|max:100',
            'alamat' => 'required|max:255',
            'kota' => 'required|max:50',
            'provinsi' => 'required|max:50',
        ]);

        Penempatan::create($validated);

        return redirect()->route('penempatan.index')->with('success', 'Penempatan berhasil ditambahkan.');
    }

    public function edit(Penempatan $penempatan)
    {
        return view('penempatan.edit', compact('penempatan'));
    }

    public function update(Request $request, Penempatan $penempatan)
    {
        $validated = $request->validate([
            'kode_kantor' => 'required|unique:penempatans,kode_kantor,' . $penempatan->id . '|max:50',
            'nama_kantor' => 'required|max:100',
            'alamat' => 'required|max:255',
            'kota' => 'required|max:50',
            'provinsi' => 'required|max:50',
        ]);

        $penempatan->update($validated);

        return redirect()->route('penempatan.index')->with('success', 'Penempatan berhasil diperbarui.');
    }

    public function destroy(Penempatan $penempatan)
    {
        $penempatan->delete();
        return redirect()->route('penempatan.index')->with('success', 'Penempatan berhasil dihapus.');
    }
}
