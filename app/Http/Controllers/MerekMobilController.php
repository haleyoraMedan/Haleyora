<?php

namespace App\Http\Controllers;

use App\Models\MerekMobil;
use App\Traits\CheckRole;
use Illuminate\Http\Request;

class MerekMobilController extends Controller
{
    use CheckRole;

    /**
     * Menampilkan semua merek mobil (ADMIN & PEGAWAI)
     */
    public function index(Request $request)
    {
        // Cek role user
        $this->checkRole($request, ['admin', 'pegawai']);

        // Ambil semua data merek
        $data = MerekMobil::orderBy('nama_merek')->get();
        $user = auth()->user();

        return view('merek_mobil.index', compact('data', 'user'));
    }

    /**
     * Tambah merek mobil baru (ADMIN)
     */
    public function store(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $validated = $request->validate([
            'nama_merek' => 'required|string|max:100|unique:merek_mobil,nama_merek',
        ]);

        MerekMobil::create($validated);

        return redirect()->back()->with('success', 'Merek mobil berhasil ditambahkan');
    }

    /**
     * Update merek mobil (ADMIN)
     */
    public function update(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $merek = MerekMobil::findOrFail($id);

        $validated = $request->validate([
            'nama_merek' => 'required|string|max:100|unique:merek_mobil,nama_merek,' . $id,
        ]);

        $merek->update($validated);

        return redirect()->back()->with('success', 'Merek mobil berhasil diperbarui');
    }

    /**
     * Hapus merek mobil (ADMIN)
     */
    public function destroy(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $merek = MerekMobil::findOrFail($id);
        // soft-delete with timestamp
        $merek->update(['is_deleted' => \Illuminate\Support\Carbon::now()]);

        return redirect()->back()->with('success', 'Merek mobil berhasil dihapus');
    }

    /**
     * BULK SOFT DELETE FOR MEREK
     */
    public function bulkDestroy(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu merek untuk dihapus');
        }

        $deleted = 0;
        foreach ($ids as $id) {
            try {
                $m = MerekMobil::find($id);
                if (!$m) continue;
                $m->update(['is_deleted' => \Illuminate\Support\Carbon::now()]);
                $deleted++;
            } catch (\Exception $e) { continue; }
        }

        return redirect()->back()->with('success', "$deleted merek berhasil dihapus");
    }
}
