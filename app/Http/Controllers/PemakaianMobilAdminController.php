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
use App\Exports\PemakaianMobilExport;

class PemakaianMobilAdminController extends Controller
{
    // Daftar semua pemakaian untuk admin, dengan filter, search, pagination
    public function daftar(Request $request)
    {
        // Auto-update status untuk pemakaian yang sudah expired
        $now = Carbon::now()->format('Y-m-d');
        PemakaianMobil::where('tanggal_selesai', '<', $now)
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'available']);

        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $date_from = $request->input('date_from', '');
        $date_to = $request->input('date_to', '');

        $query = PemakaianMobil::with(['mobil.merek', 'detail', 'fotoKondisiPemakaian', 'user']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('tujuan', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('nip', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('mobil', function($mq) use ($search) {
                      $mq->where('no_polisi', 'like', '%' . $search . '%');
                  });
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Filter by tanggal_mulai range if provided
        if (!empty($date_from)) {
            $query->whereDate('tanggal_mulai', '>=', $date_from);
        }
        if (!empty($date_to)) {
            $query->whereDate('tanggal_mulai', '<=', $date_to);
        }

        $pemakaian = $query->orderBy('created_at', 'desc')->paginate(10);
        $notifikasi = PemakaianMobil::where('status', 'pending')->count();

        // Handle AJAX request
        if ($request->ajax()) {
            $html = view('admin.pemakaian.partials.table', compact('pemakaian'))->render();
            return response()->json([
                'html' => $html,
                'notifikasi' => $notifikasi,
                'success' => true
            ]);
        }

        return view('admin.pemakaian.daftar', compact('pemakaian', 'search', 'status', 'notifikasi', 'date_from', 'date_to'));
    }

    // Dedicated AJAX endpoint: return only table partial HTML with pagination
    public function list(Request $request)
    {
        // Auto-update status untuk pemakaian yang sudah expired
        $now = Carbon::now()->format('Y-m-d');
        PemakaianMobil::where('tanggal_selesai', '<', $now)
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'available']);

        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $date_from = $request->input('date_from', '');
        $date_to = $request->input('date_to', '');

        $query = PemakaianMobil::with(['mobil.merek', 'detail', 'fotoKondisiPemakaian', 'user']);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('tujuan', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('nip', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('mobil', function($mq) use ($search) {
                      $mq->where('no_polisi', 'like', '%' . $search . '%');
                  });
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($date_from)) {
            $query->whereDate('tanggal_mulai', '>=', $date_from);
        }
        if (!empty($date_to)) {
            $query->whereDate('tanggal_mulai', '<=', $date_to);
        }

        $pemakaian = $query->orderBy('created_at', 'desc')->paginate(10);
        $notifikasi = PemakaianMobil::where('status', 'pending')->count();

        // Render hanya table partial HTML
        $html = view('admin.pemakaian.partials.table', compact('pemakaian'))->render();

        return response()->json([
            'html' => $html,
            'notifikasi' => $notifikasi,
            'success' => true
        ]);
    }

    // Ubah status pemakaian
    public function ubahStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,approved,rejected,available',
            ]);

            $pemakaian = PemakaianMobil::findOrFail($id);
            $oldStatus = $pemakaian->status;
            $newStatus = $request->status;

            // Only update if status actually changes
            if ($oldStatus !== $newStatus) {
                $pemakaian->status = $newStatus;
                $pemakaian->save();

                // Log activity — use authenticated user's numeric primary key
                $authUser = Auth::user();
                $userId = $authUser && isset($authUser->id) ? $authUser->id : null;
                PemakaianActivity::create([
                    'pemakaian_id' => $pemakaian->id,
                    'user_id' => $userId,
                    'action' => 'status_changed',
                    'data' => ['old_status' => $oldStatus, 'new_status' => $newStatus]
                ]);

                // Send notifications for status change
                $this->notifyStatusChange($pemakaian, $oldStatus, $newStatus);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'status' => $pemakaian->status,
                    'message' => 'Status berhasil diubah.'
                ]);
            }

            return redirect()->back()->with('success', 'Status pemakaian berhasil diubah.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengubah status: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withErrors('Gagal mengubah status: ' . $e->getMessage());
        }
    }

    // Helper to send status change notifications
    private function notifyStatusChange($pemakaian, $oldStatus, $newStatus)
    {
        try {
            $mobil = $pemakaian->mobil;
            $pending = PemakaianMobil::where('status', 'pending')->count();
            $statusLabel = ucfirst($newStatus);

            $payload = [
                'title' => '⚠️ Perubahan Status Pemakaian',
                'body' => "Mobil {$mobil->no_polisi} - Status: {$statusLabel}",
                'details' => "Tujuan: {$pemakaian->tujuan}",
                'pending_count' => $pending,
                'url' => url('/admin/pemakaian'),
                'tag' => 'pemakaian-notif-' . $pemakaian->id,
                'sound' => true
            ];

            // Send push notification
            SendPushNotification::dispatch($payload);

            // Store database notification for admins
            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new PemakaianNotification($payload));
            }
        } catch (\Exception $e) {
            // Log but don't fail
        }
    }

    // Detail pemakaian (untuk modal admin)
    public function detail($id)
    {
        $pemakaian = PemakaianMobil::with(['mobil.merek', 'detail', 'fotoKondisiPemakaian', 'user'])
            ->findOrFail($id);

        $detail = $pemakaian->detail ? $pemakaian->detail->toArray() : [];
        $foto = $pemakaian->fotoKondisiPemakaian ? $pemakaian->fotoKondisiPemakaian->toArray() : [];

        $user = $pemakaian->user;

        $displayName = $user->name ?: ($user->username ?? ($user->nip ?? '-'));

        return response()->json([
            'id' => $pemakaian->id,
            'user' => [
                'id' => $user->id,
                'name' => $displayName,
                'nip' => $user->nip ?? '-',
                'email' => $user->email ?? '',
                'role' => ucfirst($user->role)
            ],
            'mobil' => [
                'id' => $pemakaian->mobil->id,
                'no_polisi' => $pemakaian->mobil->no_polisi,
                'merek' => ['nama_merek' => $pemakaian->mobil->merek->nama_merek ?? ''],
                'tipe' => $pemakaian->mobil->tipe ?? '-',
                'kantor' => $pemakaian->mobil->penempatan->nama_kantor ?? '-',
                'kota' => $pemakaian->mobil->penempatan->kota ?? '-',
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

    /**
     * Export selected pemakaian as Excel (XLSX format with proper formatting).
     * Accepts `ids` array in POST body. If empty, export current filtered list.
     */
    public function export(Request $request)
    {
        $ids = $request->input('ids', []);

        // include foto relations so export can embed images
        $query = PemakaianMobil::with(['mobil.merek', 'detail', 'fotoKondisiPemakaian', 'user']);
        if (is_array($ids) && count($ids) > 0) {
            $query->whereIn('id', $ids);
        }

        $pemakaian = $query->orderBy('created_at', 'desc')->get();

        $filename = 'pemakaian_export_' . date('Ymd_His') . '.xlsx';
        
        // Use the export class to generate Excel
        $export = new PemakaianMobilExport($pemakaian);
        $export->download($filename);
    }

    /**
     * Bulk delete selected pemakaian. Accepts `ids` array.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.'], 400);
        }

        $user = auth()->user();
        $deleted = 0;

        foreach ($ids as $id) {
            try {
                $pem = PemakaianMobil::with('fotoKondisiPemakaian')->find($id);
                if (!$pem) continue;

                // authorization: admin can delete all; penempatan role maybe allow? keep admin only for safety
                if ($user->role !== 'admin') continue;

                // delete photos
                foreach ($pem->fotoKondisiPemakaian as $foto) {
                    foreach (['foto_sebelum', 'foto_sesudah'] as $col) {
                        if (!empty($foto->$col)) {
                            $path = str_replace(asset(''), '', $foto->$col);
                            $full = public_path($path);
                            if (file_exists($full)) {
                                try { unlink($full); } catch (\Exception $e) {}
                            }
                        }
                    }
                    try { $foto->delete(); } catch (\Exception $e) {}
                }

                $pem->delete();
                $deleted++;
            } catch (\Exception $e) {
                // skip error but continue
                continue;
            }
        }

        return response()->json(['success' => true, 'message' => "Berhasil menghapus {$deleted} item."]);        
    }

    // Endpoint untuk polling: cek apakah ada pemakaian baru atau update sejak timestamp terakhir
    public function checkNew(Request $request)
    {
        $last = $request->input('last_check'); // ISO timestamp

        // Count pemakaian baru ATAU pemakaian yang diupdate sejak last_check
        $newCount = 0;
        if ($last) {
            try {
                $dt = Carbon::parse($last);

                // Pemakaian yang DIBUAT setelah last_check
                $newCreated = PemakaianMobil::where('created_at', '>', $dt)->count();

                // Pemakaian yang DI-UPDATE (termasuk status change) setelah last_check
                $newUpdated = PemakaianMobil::where('updated_at', '>', $dt)->count();

                // Total item baru/update
                $newCount = max($newCreated, $newUpdated); // gunakan max untuk avoid double count
                if ($newCount === 0 && ($newCreated > 0 || $newUpdated > 0)) {
                    $newCount = 1; // at least 1 jika ada perubahan
                }
            } catch (\Exception $e) {
                $newCount = PemakaianMobil::count();
            }
        } else {
            $newCount = PemakaianMobil::count();
        }

        // total pending untuk badge
        $pending = PemakaianMobil::where('status', 'pending')->count();

        return response()->json([
            'new' => $newCount > 0 ? 1 : 0, // simplified: 1 or 0
            'pending' => $pending,
            'server_time' => Carbon::now()->toIso8601String(),
        ]);
    }
}
