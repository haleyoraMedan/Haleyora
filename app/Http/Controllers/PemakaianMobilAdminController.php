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
use App\Jobs\SendPushNotification;
use App\Models\PemakaianActivity;
use App\Models\User;
use App\Notifications\PemakaianNotification;
use Illuminate\Support\Facades\Notification;

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

    // Dedicated AJAX endpoint: return only table partial HTML for polling
    public function list(Request $request)
    {
        // Cek dan update status pemakaian yang sudah lewat tanggal_selesai
        $now = Carbon::now()->format('Y-m-d');
        PemakaianMobil::where('tanggal_selesai', '<', $now)
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'available']);

        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $query = PemakaianMobil::with(['mobil.merek', 'detail', 'fotoKondisiPemakaian', 'user']);

        if(!empty($search)) {
            $query->where('tujuan', 'like', '%'.$search.'%');
        }

        if(!empty($status)) {
            $query->where('status', $status);
        }

        $pemakaian = $query->orderBy('created_at', 'desc')->paginate(10);

        // Return only the table partial HTML
        $html = view('admin.pemakaian.partials.table', compact('pemakaian'))->render();
        return response()->json(['html' => $html]);
    }

    // Ubah status pemakaian
    public function ubahStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,available',
        ]);

        $pemakaian = PemakaianMobil::findOrFail($id);
        $oldStatus = $pemakaian->status;
        $pemakaian->status = $request->status;
        $pemakaian->save();

        // log activity and notify admins for any status change
        try {
            PemakaianActivity::create([
                'pemakaian_id' => $pemakaian->id,
                'user_id' => Auth::id(),
                'action' => 'status_changed',
                'data' => ['old_status' => $oldStatus, 'new_status' => $pemakaian->status]
            ]);

            // Notifikasi untuk semua perubahan status
            $pending = PemakaianMobil::where('status','pending')->count();
            $mobil = $pemakaian->mobil;
            $statusLabel = ucfirst($pemakaian->status);
            
            $payload = [
                'title' => '⚠️ Perubahan Status Pemakaian',
                'body' => "Mobil {$mobil->no_polisi} - Status berubah menjadi: {$statusLabel}",
                'details' => "Tujuan: {$pemakaian->tujuan}",
                'pending_count' => $pending,
                'url' => url('/admin/pemakaian'),
                'tag' => 'pemakaian-notif-' . $pemakaian->id,
                'sound' => true
            ];
            
            SendPushNotification::dispatch($payload);

            $admins = User::where('role','admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new PemakaianNotification($payload));
            }
        } catch (\Exception $e) {}

        if ($request->ajax()) {
            return response()->json(['success' => true, 'status' => $pemakaian->status]);
        }

        return redirect()->back()->with('success', 'Status pemakaian berhasil diubah.');
    }

    // Detail pemakaian (untuk modal admin)
    public function detail($id)
    {
        $pemakaian = PemakaianMobil::with(['mobil.merek', 'detail', 'fotoKondisiPemakaian', 'user'])
            ->findOrFail($id);

        $detail = $pemakaian->detail ? $pemakaian->detail->toArray() : [];
        $foto = $pemakaian->fotoKondisiPemakaian ? $pemakaian->fotoKondisiPemakaian->toArray() : [];

        return response()->json([
            'id' => $pemakaian->id,
            'user' => [
                'id' => $pemakaian->user->id,
                'name' => $pemakaian->user->name,
                'nip' => $pemakaian->user->nip ?? '-',
                'email' => $pemakaian->user->email,
                'role' => ucfirst($pemakaian->user->role)
            ],
            'mobil' => [
                'id' => $pemakaian->mobil->id,
                'no_polisi' => $pemakaian->mobil->no_polisi,
                'merek' => ['nama_merek' => $pemakaian->mobil->merek->nama_merek ?? ''],
                'tipe' => $pemakaian->mobil->tipe ?? '-'
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
            'created_at' => $pemakaian->created_at->format('d/m/Y H:i'),
            'detail' => $detail,
            'foto_kondisi' => $foto
        ]);
    }

    // Endpoint untuk polling: cek apakah ada pemakaian baru sejak timestamp terakhir
    public function checkNew(Request $request)
    {
        $last = $request->input('last_check'); // ISO timestamp


        // Count pemakaian baru OR pemakaian yang berubah status menjadi 'available' since last_check
        if ($last) {
            try {
                $dt = Carbon::parse($last);

                $newCount = PemakaianMobil::where(function($q) use ($dt) {
                    $q->where('created_at', '>', $dt)
                      ->orWhere(function($q2) use ($dt) {
                          $q2->where('updated_at', '>', $dt)
                             ->where('status', 'available');
                      });
                })->count();
            } catch (\Exception $e) {
                $newCount = PemakaianMobil::count();
            }
        } else {
            $newCount = PemakaianMobil::count();
        }

        // total pending untuk badge
        $pending = PemakaianMobil::where('status', 'pending')->count();

        return response()->json([
            'new' => $newCount,
            'pending' => $pending,
            'server_time' => Carbon::now()->toIsoString(),
        ]);
    }
}
