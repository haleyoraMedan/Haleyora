<?php

namespace App\Http\Controllers;

use App\Models\MerekMobil;
use App\Traits\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
        $data = MerekMobil::whereNull('is_deleted')->orderBy('nama_merek')->get();
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
        // If there are cars using this brand, perform a soft-delete (hide the brand)
        $hasChildren = \App\Models\Mobil::where('merek_id', $merek->id)->exists();
        if ($hasChildren) {
            $merek->update(['is_deleted' => Carbon::now()]);
            return redirect()->back()->with('success', 'Merek memiliki mobil terkait — merek disembunyikan (soft delete).');
        }

        // No related cars — safe to hard delete
        $merek->delete();

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
                // If children exist, soft-delete (hide); otherwise delete
                $hasChildren = \App\Models\Mobil::where('merek_id', $m->id)->exists();
                try {
                    if ($hasChildren) {
                        $m->update(['is_deleted' => Carbon::now()]);
                    } else {
                        $m->delete();
                    }
                    $deleted++;
                } catch (\Exception $e) { continue; }
            } catch (\Exception $e) { continue; }
        }

        return redirect()->back()->with('success', "$deleted merek berhasil dihapus");
    }
}
