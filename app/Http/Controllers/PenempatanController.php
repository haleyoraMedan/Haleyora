<?php

namespace App\Http\Controllers;

use App\Models\Penempatan;
use App\Traits\CheckRole;
use Illuminate\Http\Request;

class PenempatanController extends Controller
{
    use CheckRole;

    /**
     * Index + search + edit form
     */
    public function index(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $search = $request->query('q');
        $editId = $request->query('edit');
        $editPenempatan = $editId ? Penempatan::find($editId) : null;

        $penempatan = Penempatan::when($search, function($query) use ($search) {
            $query->where('kode_kantor', 'like', "%$search%")
                  ->orWhere('nama_kantor', 'like', "%$search%")
                  ->orWhere('alamat', 'like', "%$search%")
                  ->orWhere('kota', 'like', "%$search%")
                  ->orWhere('provinsi', 'like', "%$search%");
        })->orderBy('nama_kantor')->get();

        $user = auth()->user();
        return view('penempatan.index', compact('penempatan', 'search', 'editPenempatan', 'user'));
    }

    public function show(Request $request, $id)
    {
        $penempatan = Penempatan::findOrFail($id);

        // Jika diminta JSON (AJAX), kembalikan JSON untuk dipakai modal
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($penempatan);
        }

        $user = auth()->user();
        return view('penempatan.show', compact('penempatan', 'user'));
    }


    /**
     * Store
     */
    public function store(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $validated = $request->validate([
            'kode_kantor' => 'required|string|max:10|unique:penempatan,kode_kantor',
            'nama_kantor' => 'required|string|max:100',
            'alamat'      => 'required|string|max:255',
            'kota'        => 'required|string|max:50',
            'provinsi'    => 'required|string|max:50',
        ]);

        Penempatan::create($validated);

        return redirect()->back()->with('success', 'Penempatan berhasil ditambahkan');
    }

    /**
     * Update
     */
    public function update(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $penempatan = Penempatan::findOrFail($id);

        $validated = $request->validate([
            'kode_kantor' => 'required|string|max:10|unique:penempatan,kode_kantor,' . $id,
            'nama_kantor' => 'required|string|max:100',
            'alamat'      => 'required|string|max:255',
            'kota'        => 'required|string|max:50',
            'provinsi'    => 'required|string|max:50',
        ]);

        $penempatan->update($validated);

        return redirect()->back()->with('success', 'Penempatan berhasil diperbarui');
    }

    /**
     * Delete
     */
    public function destroy($id)
    {
        $this->checkRole(request(), ['admin']);

        $penempatan = Penempatan::findOrFail($id);
        $penempatan->delete();

        return redirect()->back()->with('success', 'Penempatan berhasil dihapus');
    }
}
