<?php

namespace App\Http\Controllers;

use App\Models\Mobil;
use App\Models\Penempatan;
use App\Models\MerekMobil;
use App\Models\JenisMobil;
use App\Traits\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MobilController extends Controller
{
    use CheckRole;

    public function index(Request $request)
{
    $query = Mobil::with(['merek', 'jenis', 'penempatan']);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where('no_polisi', 'like', "%$search%")
              ->orWhereHas('merek', fn($q) => $q->where('nama_merek','like',"%$search%"))
              ->orWhere('tipe', 'like', "%$search%");
    }

    $mobils = $query->orderBy('no_polisi')->paginate(10); // 10 data per halaman

    $penempatans = Penempatan::all();
    $merek = MerekMobil::all();
    $jenis = JenisMobil::all();

    $user = auth()->user();
    return view('mobil.index', compact('mobils', 'penempatans', 'merek', 'jenis', 'user'));
}

    // EDIT FORM + TAMPIL DI INDEX
    public function edit(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $mobil = Mobil::whereNull('is_deleted')->findOrFail($id);
        $mobils = Mobil::whereNull('is_deleted')->orderBy('no_polisi')->get();
        $penempatans = Penempatan::orderBy('nama_kantor')->get();
        $merek = MerekMobil::orderBy('nama_merek')->get();
        $jenis = JenisMobil::orderBy('nama_jenis')->get();

        $user = auth()->user();
        return view('mobil.index', compact('mobils', 'mobil', 'penempatans', 'merek', 'jenis', 'user'));
    }

    // STORE MOBIL
    public function store(Request $request)
    {
        $this->checkRole($request, ['admin']);

        $validated = $request->validate([
            'no_polisi'     => 'required|string|max:20|unique:mobil,no_polisi',
            'merek_id'      => 'required|integer|exists:merek_mobil,id',
            'jenis_id'      => 'required|integer|exists:jenis_mobil,id',
            'tipe'          => 'required|string|max:50',
            'tahun'         => 'required|integer',
            'warna'         => 'required|string|max:20',
            'no_rangka'     => 'required|string|max:50',
            'no_mesin'      => 'required|string|max:50',
            'penempatan_id' => 'nullable|integer|exists:penempatan,id',
        ]);

        Mobil::create($validated);

        return redirect()->route('mobil.index')->with('success', 'Mobil berhasil ditambahkan');
    }

    // UPDATE MOBIL
    public function update(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $mobil = Mobil::whereNull('is_deleted')->findOrFail($id);

        $validated = $request->validate([
            'no_polisi'     => 'required|string|max:20|unique:mobil,no_polisi,' . $id,
            'merek_id'      => 'required|integer|exists:merek_mobil,id',
            'jenis_id'      => 'required|integer|exists:jenis_mobil,id',
            'tipe'          => 'required|string|max:50',
            'tahun'         => 'required|integer',
            'warna'         => 'required|string|max:20',
            'no_rangka'     => 'required|string|max:50',
            'no_mesin'      => 'required|string|max:50',
            'penempatan_id' => 'nullable|integer|exists:penempatan,id',
        ]);

        $mobil->update($validated);

        return redirect()->route('mobil.index')->with('success', 'Mobil berhasil diperbarui');
    }

    // SOFT DELETE
    public function destroy(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $mobil = Mobil::whereNull('is_deleted')->findOrFail($id);
        $mobil->update(['is_deleted' => Carbon::now()]);

        return redirect()->route('mobil.index')->with('success', 'Mobil berhasil dihapus');
    }

    // RESTORE
    public function restore(Request $request, $id)
    {
        $this->checkRole($request, ['admin']);

        $mobil = Mobil::whereNotNull('is_deleted')->findOrFail($id);
        $mobil->update(['is_deleted' => null]);

        return redirect()->route('mobil.index')->with('success', 'Mobil berhasil direstore');
    }
}
