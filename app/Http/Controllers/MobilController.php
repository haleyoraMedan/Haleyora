<?php

namespace App\Http\Controllers;

use App\Models\Mobil;
use App\Models\LaporanRusak;
use App\Models\LaporanRusakFoto;
use App\Models\Penempatan;
use App\Models\MerekMobil;
use App\Models\JenisMobil;
use App\Traits\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MobilController extends Controller
{
    use CheckRole;

    /**
     * INDEX + SEARCH (AKTIF + DELETED)
     */
    public function index(Request $request)
    {
        $query = Mobil::with(['merek', 'jenis', 'penempatan', 'laporanRusak']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('no_polisi', 'like', "%$search%")
                  // removed 'tipe' field from search per request
                  ->orWhereHas('merek', function ($m) use ($search) {
                      $m->where('nama_merek', 'like', "%$search%");
                  });
            });
        }

        // Jika diminta, tampilkan hanya data yang sudah dihapus
        if ($request->query('show_deleted') == '1') {
            $query->whereNotNull('is_deleted');
        }

        $mobils = $query
            ->orderByRaw('is_deleted IS NOT NULL') // aktif di atas
            ->when($request->filled('sort') && $request->sort == 'kondisi', function ($q) use ($request) {
                $dir = $request->get('dir', 'asc') === 'desc' ? 'desc' : 'asc';
                $q->orderByRaw("(select kondisi from detail_mobil where mobil_id = mobil.id) $dir");
            })
            ->orderBy('no_polisi')
            ->paginate(10);

        return view('mobil.index', compact('mobils'));
    }

    /**
     * Set mobil condition back to available (admin only)
     */
    public function setAvailable(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $mobil = Mobil::findOrFail($id);

        try {
            $detail = $mobil->detail;
            if (! $detail) {
                // Create detail record if missing
                $detail = $mobil->detail()->create(['kondisi' => 'available']);
            } else {
                $detail->update(['kondisi' => 'available']);
            }

            // Restore mobil availability flag
            try {
                $mobil->is_deleted = null;
                $mobil->save();
            } catch (\Exception $e) {
                // ignore
            }

            // Remove any existing laporan and related photos for this mobil
            try {
                $laporans = LaporanRusak::where('mobil_id', $mobil->id)->get();
                foreach ($laporans as $lap) {
                    // delete fotos
                    LaporanRusakFoto::where('laporan_rusak_id', $lap->id)->delete();
                    $lap->delete();
                }
            } catch (\Exception $e) {
                // ignore
            }

            return redirect()->route('mobil.index')->with('success', 'Mobil ditandai tersedia (kondisi: Baik)');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui kondisi: ' . $e->getMessage());
        }
    }

    /**
     * CREATE FORM
     */
    public function create(Request $request)
    {
        $this->checkRole($request, ['admin']);

        return view('mobil.create', [
            'penempatans' => Penempatan::orderBy('nama_kantor')->get(),
            'merek'       => MerekMobil::orderBy('nama_merek')->get(),
            'jenis'       => JenisMobil::orderBy('nama_jenis')->get(),
        ]);
    }

    /**
     * EDIT FORM (HANYA DATA AKTIF)
     */
    public function edit(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $mobil = Mobil::whereNull('is_deleted')->findOrFail($id);

        return view('mobil.edit', [
            'mobil'       => $mobil,
            'penempatans' => Penempatan::orderBy('nama_kantor')->get(),
            'merek'       => MerekMobil::orderBy('nama_merek')->get(),
            'jenis'       => JenisMobil::orderBy('nama_jenis')->get(),
        ]);
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $validated = $request->validate([
            'no_polisi'     => 'required|string|max:20|unique:mobil,no_polisi',
            'merek_id'      => 'required|exists:merek_mobil,id',
            'jenis_id'      => 'required|exists:jenis_mobil,id',
            'tahun'         => 'required|integer',
            'warna'         => 'required|string|max:20',
            'no_rangka'     => 'required|string|max:50',
            'no_mesin'      => 'required|string|max:50',
            'penempatan_id' => 'nullable|exists:penempatan,id',
        ]);

        Mobil::create($validated);

        return redirect()
            ->route('mobil.index')
            ->with('success', 'Mobil berhasil ditambahkan');
    }

    /**
     * UPDATE (HANYA DATA AKTIF)
     */
    public function update(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $mobil = Mobil::whereNull('is_deleted')->findOrFail($id);

        $validated = $request->validate([
            'no_polisi'     => 'required|string|max:20|unique:mobil,no_polisi,' . $id,
            'merek_id'      => 'required|exists:merek_mobil,id',
            'jenis_id'      => 'required|exists:jenis_mobil,id',
            'tahun'         => 'required|integer',
            'warna'         => 'required|string|max:20',
            'no_rangka'     => 'required|string|max:50',
            'no_mesin'      => 'required|string|max:50',
            'penempatan_id' => 'nullable|exists:penempatan,id',
        ]);

        $mobil->update($validated);

        return redirect()
            ->route('mobil.index')
            ->with('success', 'Mobil berhasil diperbarui');
    }

    /**
     * SOFT DELETE (HANYA DATA AKTIF)
     */
    public function destroy(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        try {
            $mobil = Mobil::whereNull('is_deleted')->findOrFail($id);

            // Check if mobil is being used in pemakaian
            $sedangDigunakan = $mobil->pemakaian()
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($sedangDigunakan) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak bisa menghapus mobil yang sedang digunakan.'
                    ], 400);
                }
                return redirect()
                    ->back()
                    ->with('error', 'Tidak bisa menghapus mobil yang sedang digunakan. Selesaikan pemakaian terlebih dahulu.');
            }

            $mobil->update([
                'is_deleted' => Carbon::now(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mobil berhasil dihapus'
                ]);
            }

            return redirect()
                ->route('mobil.index')
                ->with('success', 'Mobil berhasil dihapus (soft delete)');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withErrors('Gagal menghapus mobil: ' . $e->getMessage());
        }
    }

    /**
     * RESTORE (MENGEMBALIKAN DATA YANG DIHAPUS)
     */
    public function restore(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        try {
            $mobil = Mobil::whereNotNull('is_deleted')->findOrFail($id);

            $mobil->update([
                'is_deleted' => null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mobil berhasil direstore'
                ]);
            }

            return redirect()
                ->route('mobil.index')
                ->with('success', 'Mobil berhasil direstore');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal direstore: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withErrors('Gagal direstore mobil: ' . $e->getMessage());
        }
    }

    /**
     * PERMANENT DELETE (HAPUS PERMANEN)
     */
    public function forceDelete(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        try {
            $mobil = Mobil::findOrFail($id);

            // Check if mobil has active pemakaian
            $sedangDigunakan = $mobil->pemakaian()
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($sedangDigunakan) {
                return redirect()
                    ->back()
                    ->with('error', 'Tidak bisa menghapus permanen mobil yang sedang digunakan.');
            }

            $noPolisi = $mobil->no_polisi;
            $mobil->delete(); // Permanent delete

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mobil ' . $noPolisi . ' berhasil dihapus permanen'
                ]);
            }

            return redirect()
                ->route('mobil.index')
                ->with('success', 'Mobil ' . $noPolisi . ' berhasil dihapus permanen');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus permanen: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withErrors('Gagal menghapus mobil: ' . $e->getMessage());
        }
    }

    /**
     * BULK SOFT DELETE (HAPUS TERPILIH)
     */
    public function bulkDestroy(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu mobil untuk dihapus');
        }

        $skipped = [];
        $deleted = 0;

        foreach ($ids as $id) {
            try {
                $mobil = Mobil::find($id);
                if (!$mobil) continue;

                $sedangDigunakan = $mobil->pemakaian()
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($sedangDigunakan) {
                    $skipped[] = $mobil->no_polisi;
                    continue;
                }

                $mobil->update(['is_deleted' => Carbon::now()]);
                $deleted++;
            } catch (\Exception $e) {
                // skip on error
                continue;
            }
        }

        $msg = "$deleted mobil berhasil dihapus.";
        if ($skipped) {
            $msg .= ' Beberapa mobil tidak dihapus karena sedang digunakan: ' . implode(', ', $skipped);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * DETAIL KERUSAKAN (ADMIN ONLY)
     */
    public function detailKerusakan(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $mobil = Mobil::with(['laporanRusak.fotos', 'merek', 'jenis', 'penempatan'])->findOrFail($id);

        // Redirect to index if there is no laporan kerusakan for this mobil
        if (!isset($mobil->laporanRusak) || $mobil->laporanRusak->isEmpty()) {
            return redirect()->route('mobil.index');
        }

        return view('mobil.detail-kerusakan', compact('mobil'));
    }
}
