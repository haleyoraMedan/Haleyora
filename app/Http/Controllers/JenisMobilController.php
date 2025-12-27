<?php

namespace App\Http\Controllers;

use App\Models\JenisMobil;
use App\Traits\CheckRole;
use Illuminate\Http\Request;

class JenisMobilController extends Controller
{
    use CheckRole;

    /**
     * TAMPILAN LIST JENIS MOBIL
     */
    public function index(Request $request)
    {
        $user = $this->checkRole($request, ['admin', 'pegawai']);

        $data = JenisMobil::orderBy('nama_jenis')->get();

        return view('jenis_mobil.index', compact('data', 'user'));
    }

    /**
     * SIMPAN DATA (ADMIN)
     */
    public function store(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $request->validate([
            'nama_jenis' => 'required|string|max:100|unique:jenis_mobil,nama_jenis',
        ]);

        JenisMobil::create([
            'nama_jenis' => $request->nama_jenis
        ]);

        return redirect()->route('jenis-mobil.index')
            ->with('success', 'Jenis mobil berhasil ditambahkan');
    }

    /**
     * UPDATE DATA (ADMIN)
     */
    public function update(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $request->validate([
            'nama_jenis' => 'required|string|max:100|unique:jenis_mobil,nama_jenis,' . $id,
        ]);

        JenisMobil::findOrFail($id)->update([
            'nama_jenis' => $request->nama_jenis
        ]);

        return redirect()->route('jenis-mobil.index')
            ->with('success', 'Jenis mobil berhasil diperbarui');
    }

    /**
     * HAPUS DATA (ADMIN)
     */
    public function destroy(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        JenisMobil::findOrFail($id)->delete();

        return redirect()->route('jenis-mobil.index')
            ->with('success', 'Jenis mobil berhasil dihapus');
    }
}
