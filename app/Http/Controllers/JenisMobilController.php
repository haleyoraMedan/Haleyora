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
        $jm = JenisMobil::findOrFail($id);
        $jm->update(['is_deleted' => \Illuminate\Support\Carbon::now()]);

        return redirect()->route('jenis-mobil.index')
            ->with('success', 'Jenis mobil berhasil dihapus');
    }

    /**
     * BULK SOFT DELETE FOR JENIS
     */
    public function bulkDestroy(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu jenis untuk dihapus');
        }

        $deleted = 0;
        foreach ($ids as $id) {
            try {
                $m = JenisMobil::find($id);
                if (!$m) continue;
                $m->update(['is_deleted' => \Illuminate\Support\Carbon::now()]);
                $deleted++;
            } catch (\Exception $e) { continue; }
        }

        return redirect()->back()->with('success', "$deleted jenis berhasil dihapus");
    }
}
