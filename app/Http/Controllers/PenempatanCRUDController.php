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
            'kode_kantor' => 'required|unique:penempatan|max:50',
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
            'kode_kantor' => 'required|unique:penempatan,kode_kantor,' . $penempatan->id . '|max:50',
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
        // soft-delete using timestamp
        $penempatan->update(['is_deleted' => \Illuminate\Support\Carbon::now()]);
        return redirect()->route('penempatan.index')->with('success', 'Penempatan berhasil dihapus.');
    }

    /**
     * BULK SOFT DELETE PENEMPATAN
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu penempatan untuk dihapus');
        }

        $deleted = 0;
        foreach ($ids as $id) {
            try {
                $p = Penempatan::find($id);
                if (!$p) continue;
                $p->update(['is_deleted' => \Illuminate\Support\Carbon::now()]);
                $deleted++;
            } catch (\Exception $e) { continue; }
        }

        return redirect()->back()->with('success', "$deleted penempatan berhasil dihapus");
    }
}
