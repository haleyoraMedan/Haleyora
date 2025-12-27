<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mobil;
use App\Models\PemakaianMobil;
use App\Models\DetailMobil;
use App\Models\FotoKondisiPemakaian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PemakaianMobilAdminController extends Controller
{
    // Daftar semua pemakaian untuk admin, dengan filter, search, pagination
    public function daftar(Request $request)
    {
        // Cek dan update status pemakaian yang sudah lewat tanggal_selesai
        $now = Carbon::now()->format('Y-m-d');
        PemakaianMobil::where('tanggal_selesai', '<', $now)
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'available']);

        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $query = PemakaianMobil::with(['mobil.merek', 'detail', 'fotoKondisiPemakaian']);

        if(!empty($search)) {
            $query->where('tujuan', 'like', '%'.$search.'%');
        }

        if(!empty($status)) {
            $query->where('status', $status);
        }

        $pemakaian = $query->orderBy('created_at', 'desc')->paginate(10);

        // Hitung jumlah pemakaian baru (pending) untuk notifikasi
        $notifikasi = PemakaianMobil::where('status', 'pending')->count();

        if ($request->ajax()) {
            $html = view('admin.pemakaian.partials.table', compact('pemakaian'))->render();
            return response()->json(['html' => $html, 'notifikasi' => $notifikasi]);
        }

        return view('admin.pemakaian.daftar', compact('pemakaian', 'search', 'status', 'notifikasi'));
    }

    // Ubah status pemakaian
    public function ubahStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,available',
        ]);

        $pemakaian = PemakaianMobil::findOrFail($id);
        $pemakaian->status = $request->status;
        $pemakaian->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'status' => $pemakaian->status]);
        }

        return redirect()->back()->with('success', 'Status pemakaian berhasil diubah.');
    }

    // Detail pemakaian (untuk modal admin)
    public function detail($id)
    {
        $pemakaian = PemakaianMobil::with(['mobil.merek', 'detail', 'fotoKondisiPemakaian'])
            ->findOrFail($id);

        $detail = $pemakaian->detail ? $pemakaian->detail->toArray() : [];
        $foto = $pemakaian->fotoKondisiPemakaian ? $pemakaian->fotoKondisiPemakaian->toArray() : [];

        return response()->json([
            'id' => $pemakaian->id,
            'mobil' => [
                'no_polisi' => $pemakaian->mobil->no_polisi,
                'merek' => ['nama_merek' => $pemakaian->mobil->merek->nama_merek ?? '']
            ],
            'tujuan' => $pemakaian->tujuan,
            'tanggal_mulai' => $pemakaian->tanggal_mulai,
            'tanggal_selesai' => $pemakaian->tanggal_selesai,
            'jarak_tempuh_km' => $pemakaian->jarak_tempuh_km,
            'bahan_bakar' => $pemakaian->bahan_bakar ?? '-',
            'bahan_bakar_liter' => $pemakaian->bahan_bakar_liter ?? '-',
            'transmisi' => $pemakaian->transmisi ?? '-',
            'catatan' => $pemakaian->catatan ?? '-',
            'status' => $pemakaian->status,
            'detail' => $detail,
            'foto_kondisi' => $foto
        ]);
    }
}
