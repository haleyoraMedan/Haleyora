<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mobil;
use App\Models\PemakaianMobil;
use App\Models\DetailMobil;
use App\Models\FotoKondisiPemakaian;
use App\Models\LaporanRusak;
use App\Models\LaporanRusakFoto;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendPushNotification;
use App\Models\PemakaianActivity;
use App\Models\User;
use App\Notifications\PemakaianNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PemakaianMobilController extends Controller
{
  // Menampilkan daftar mobil yang tersedia untuk dipilih
  public function pilihMobil()
{
    $user = Auth::user();
    // Determine full vs ringkas window using Jakarta time
    $nowJkt = Carbon::now('Asia/Jakarta');
    $hour = (int) $nowJkt->format('H');
    $minute = (int) $nowJkt->format('i');
    $m = $hour * 60 + $minute;
    // Full input windows: 06:00-09:59 (360-599) and 16:30-19:59 (990-1199)
    $isFull = ($m >= 360 && $m < 600) || ($m >= 990 && $m < 1200);

    $mobilsQuery = Mobil::with(['merek', 'penempatan'])
      ->where('penempatan_id', $user->penempatan_id)
      // mobil yang sedang dipakai (aktif) tidak ditampilkan
      ->whereDoesntHave('pemakaian', function ($q) {
        $q->aktif();
      })
      // mobil rusak tidak ditampilkan
      ->whereDoesntHave('detail', function ($q) {
        $q->where('kondisi', 'rusak');
      })
      ->whereNull('is_deleted');

    // If full input window, exclude mobils that are already someone's mobil pegangan (except current user's own)
    if ($isFull) {
      $assigned = User::whereNotNull('mobil_id')
        ->where('mobil_id', '<>', $user->mobil_id)
        ->pluck('mobil_id')
        ->toArray();

      if (!empty($assigned)) {
        $mobilsQuery->whereNotIn('id', $assigned);
      }
    }

    $mobils = $mobilsQuery->get();

    $pilihanMobilId = session('pemilihan_mobil_id');

    return view('pemakaian.pilih_mobil', compact('mobils', 'pilihanMobilId', 'isFull'));
}


  public function inputDetail(Request $request)
  {
    // cek apakah edit
    $pemakaian = null;
    if ($request->has("edit_id")) {
      $pemakaian = PemakaianMobil::with([
        "mobil.merek",
        "detail",
        "fotoKondisiPemakaian",
      ])->findOrFail($request->edit_id);

      $user = Auth::user();
      // Allow edit if: status is pending OR status is rejected (so user can fix after admin reject)
      // Also allow admins to open the edit view for any record.
      // However, if this is a completion flow (complete=1), permit opening the input form even when status is not pending/rejected.
      $is_completion = $request->query('complete') == '1' || $request->query('complete') === 'true';
      if (!$is_completion && !in_array($pemakaian->status, ['pending', 'rejected']) && ($user->role !== 'admin')) {
        abort(404);
      }

      // Only owner or admin can edit
      if ($user->role !== 'admin' && $pemakaian->user_id !== $user->id) {
        abort(403);
      }

      // Set session untuk edit
      session(["pemilihan_mobil_id" => $pemakaian->mobil_id]);
      $mobil = $pemakaian->mobil;
    } else {
      // Untuk create, ambil dari session
      $mobilId = session("pemilihan_mobil_id");

      if (!$mobilId) {
        return redirect()
          ->route("pemakaian.pilihMobil")
          ->withErrors("Silakan pilih mobil terlebih dahulu.");
      }

      $mobil = Mobil::with(["merek", "detail"])->findOrFail($mobilId);
    }

    // Determine restriction based on Jakarta time using new schedule
    $nowJkt = Carbon::now('Asia/Jakarta');
    $hour = (int) $nowJkt->format('H');
    $minute = (int) $nowJkt->format('i');
    $m = $hour * 60 + $minute;
    // Full input windows: 06:00-09:59 (360-599) and 16:30-19:59 (990-1199)
    $is_full = ($m >= 360 && $m < 600) || ($m >= 990 && $m < 1200);
    $is_restricted = !$is_full;
    $current_time_jkt = $nowJkt->format('H:i:s');

    // detect completion mode (redirected from 'selesaikan' flow)
    $is_completion = $request->query('complete') == '1' || $request->query('complete') === 'true';

    return view("pemakaian.input_detail", compact("mobil", "pemakaian", "is_restricted", "current_time_jkt", "is_completion"));
  }

  // Simpan atau update detail pemakaian
  public function simpanDetail(Request $request, $id = null)
  {
    $user = Auth::user();

    // Time-based restriction per new schedule (Asia/Jakarta).
    $nowJkt = Carbon::now('Asia/Jakarta');
    $hourNow = (int) $nowJkt->format('H');
    $minuteNow = (int) $nowJkt->format('i');
    $mNow = $hourNow * 60 + $minuteNow;
    $is_full = ($mNow >= 360 && $mNow < 600) || ($mNow >= 990 && $mNow < 1200);
    $is_restricted = !$is_full;

    // Determine which pemakaian we're working with
    if ($id) {
      // Edit mode - get from ID. Allow fetching records for completion submissions even if status isn't pending/rejected.
      $isCompletionPost = $request->input('complete_mode') == '1' || $request->query('complete') == '1';
      if ($isCompletionPost) {
        $pemakaian = PemakaianMobil::where("id", $id)->firstOrFail();
      } else {
        $pemakaian = PemakaianMobil::where("id", $id)
          ->whereIn('status', ['pending', 'rejected'])
          ->firstOrFail();
      }
      $mobil = $pemakaian->mobil;
    } else {
      // Create mode - get from session
      $mobilId = session("pemilihan_mobil_id");
      if (!$mobilId) {
        return redirect()
          ->route("pemakaian.pilihMobil")
          ->withErrors("Silakan pilih mobil terlebih dahulu.");
      }
      $mobil = Mobil::findOrFail($mobilId);
      $pemakaian = null;
    }

    // Build validation rules depending on restricted window
    // SIM photo rule: required when creating; when editing require only if existing record has no sim_foto
    // If this is a completion flow (foto sesudah), only validate foto inputs (photos only)
    $isCompletionPost = $request->input('complete_mode') == '1' || $request->query('complete') == '1';

    if ($isCompletionPost) {
      // Completion: only accept foto uploads (sim optional/ignored)
      $rules = [
        "foto.*.posisi" => "required_with:foto.*.file|in:depan,belakang,kanan,kiri,joksabuk,acventilasi,panelaudio,lampukabin,interior_bersih,toolkitdongkrak",
        "foto.*.file" => "nullable|image|max:61440",
        // sim_foto not required/ignored in completion
        "sim_foto" => 'nullable|image|max:61440',
      ];
    } else {
      // Regular create/update rules
      if ($id) {
        $simRule = (!empty($pemakaian->sim_foto)) ? 'nullable|image|max:61440' : 'required|image|max:61440';
      } else {
        $simRule = 'required|image|max:61440';
      }

      $rules = [
        "tujuan" => "required|string|max:255",
        "kondisi_sebelum_setelah" => "required|in:sebelum pemakaian,sesudah pemakaian",
        "tanggal_mulai" => "required|date",
        "tanggal_selesai" => "required|date|after_or_equal:tanggal_mulai",
        "kilometer" => $is_restricted ? "nullable|integer" : "required|integer",
        "jarak_tempuh_km" => "nullable|numeric",
        "bahan_bakar" => "nullable|in:Bensin,Solar,Listrik",
        "bahan_bakar_liter" => "required|numeric",
        "transmisi" => "required|in:Manual,Automatic",
        "catatan" => "nullable|string",
        "depan" => $is_restricted ? "nullable|string" : "required|string",
        "belakang" => $is_restricted ? "nullable|string" : "required|string",
        "kanan" => $is_restricted ? "nullable|string" : "required|string",
        "kiri" => $is_restricted ? "nullable|string" : "required|string",
        "joksabuk" => "nullable|string",
        "acventilasi" => "nullable|string",
        "panelaudio" => "nullable|string",
        "lampukabin" => "nullable|string",
        "interior_bersih" => "nullable|string",
        "toolkitdongkrak" => "nullable|string",
        "kondisi" => "nullable|string",
        "foto.*.posisi" => "required_with:foto.*.file|in:depan,belakang,kanan,kiri,joksabuk,acventilasi,panelaudio,lampukabin,interior_bersih,toolkitdongkrak",
        "foto.*.file" => "nullable|image|max:61440",
        "sim_foto" => $simRule,
      ];
    }

    $messages = [
      "foto.*.posisi.required_with" => "Posisi foto harus diisi jika ada file foto",
      "foto.*.posisi.in" => "Posisi foto harus salah satu dari: depan, belakang, kanan, kiri, joksabuk, acventilasi, panelaudio, lampukabin, interior_bersih, toolkitdongkrak",
    ];

    $request->validate($rules, $messages);

    $isUpdate = (bool) $id;

    // Determine if this request is a completion-mode submission (photo-only)
    $completeMode = $request->input('complete_mode') == '1' || $request->input('complete_mode') === 'true' || $request->query('complete') == '1';

    DB::transaction(function () use (
      $request,
      $user,
      $mobil,
      $id,
      &$pemakaian,
      $is_restricted,
      $is_full,
      $completeMode
    ) {
      if ($id) {
        // Update existing pemakaian: allow changing main fields even in completion mode
        $pemakaian->update([
          "tujuan" => $request->tujuan,
          "kondisi_sebelum_setelah" => $request->kondisi_sebelum_setelah,
          "tanggal_mulai" => $request->tanggal_mulai,
          "tanggal_selesai" => $request->tanggal_selesai,
          "jarak_tempuh_km" => $request->jarak_tempuh_km ?? 0,
          "bahan_bakar_liter" => $request->bahan_bakar_liter ?? 0,
          "catatan" => $request->catatan,
        ]);
        // If update made by non-admin (user), set status back to pending for admin review
        if (isset($user) && ($user->role ?? '') !== 'admin') {
          $pemakaian->status = 'pending';
          // clear previous reject reason when user resubmits
          $pemakaian->alasan_reject = null;
          $pemakaian->save();
        }
      } else {
        // Create new pemakaian
        $pemakaian = PemakaianMobil::create([
          "mobil_id" => $mobil->id,
          "user_id" => $user->id,
          "tujuan" => $request->tujuan,
          "kondisi_sebelum_setelah" => $request->kondisi_sebelum_setelah,
          "tanggal_mulai" => $request->tanggal_mulai,
          "tanggal_selesai" => $request->tanggal_selesai,
          "jarak_tempuh_km" => $request->jarak_tempuh_km ?? 0,
          "bahan_bakar_liter" => $request->bahan_bakar_liter ?? 0,
          "catatan" => $request->catatan,
          "status" => "approved",
        ]);
        // store snapshot of mobil detail as "sebelum" for this pemakaian
        try {
          $pemakaian->detail_sebelum = $mobil->detail ? $mobil->detail->toArray() : [];
          $pemakaian->save();
        } catch (\Exception $e) {
          // ignore snapshot errors
        }
        // If we're in a full input window, set this mobil as the user's mobil pegangan
        if ($is_full) {
          try {
            $alreadyAssigned = \App\Models\User::whereNotNull('mobil_id')
              ->where('mobil_id', $mobil->id)
              ->where('id', '<>', $user->id)
              ->exists();

            if (!$alreadyAssigned) {
              $user->mobil_id = $mobil->id;
              $user->save();
            } else {
              // do not overwrite someone else's assignment; flash a warning
              session()->flash('warning', 'Mobil sudah menjadi pegangan driver lain; tidak diset sebagai mobil pegangan Anda.');
            }
          } catch (\Exception $e) {
            // ignore save errors
          }
        }
      }

      // Update atau buat detail mobil
      // If this is a completion submission and we don't yet have a 'sebelum' snapshot,
      // capture the current mobil detail before we overwrite it with the submitted values.
      if ($id && $completeMode) {
        try {
          $hasSebelum = !empty($pemakaian->detail_sebelum) && is_array($pemakaian->detail_sebelum) && count($pemakaian->detail_sebelum) > 0;
          if (!$hasSebelum) {
            $pemakaian->detail_sebelum = $mobil->detail ? $mobil->detail->toArray() : ($pemakaian->detail_sebelum ?? []);
            $pemakaian->save();
          }
        } catch (\Exception $e) {
          // ignore snapshot errors
        }
      }
      $detailFields = [
        "kilometer",
        "bahan_bakar",
        "transmisi",
        "kondisi",
        "depan",
        "belakang",
        "kanan",
        "kiri",
        "joksabuk",
        "acventilasi",
        "panelaudio",
        "lampukabin",
        "interior_bersih",
        "toolkitdongkrak",
      ];

      $detailData = $request->only($detailFields);

      // Debug logging: record incoming detail-related inputs and current mobil detail
      try {
        Log::info('simpanDetail: incoming detail inputs', [
          'pemakaian_id' => $id,
          'mobil_id' => $mobil->id ?? null,
          'user_id' => $user->id ?? null,
          'detail_inputs' => $request->only($detailFields),
        ]);
        $existing = $mobil->detail ? $mobil->detail->toArray() : [];
        Log::info('simpanDetail: existing mobil->detail', ['mobil_id' => $mobil->id ?? null, 'existing_detail' => $existing]);
      } catch (\Exception $e) {}

      // Ensure all expected detail fields are present to avoid DB errors
      $expected = [
        'kilometer','bahan_bakar','transmisi','kondisi',
        'depan','belakang','kanan','kiri','joksabuk','acventilasi','panelaudio','lampukabin','interior_bersih','toolkitdongkrak'
      ];

      foreach ($expected as $key) {
        if (!array_key_exists($key, $detailData) || $detailData[$key] === null) {
          if ($key === 'kilometer') {
            $detailData[$key] = $request->input('kilometer', 0);
          } else {
            // use '-' as a neutral placeholder for non-numeric fields
            $detailData[$key] = '-';
          }
        }
      }

      // If restricted period, prefer '-' for condition-related fields (already set above)

      DetailMobil::updateOrCreate([
        "mobil_id" => $mobil->id
      ], $detailData);

      // After save, log what was saved and where kilometer likely came from
      try {
        $saved = DetailMobil::where('mobil_id', $mobil->id)->first();
        Log::info('simpanDetail: saved detail mobil', ['mobil_id' => $mobil->id, 'saved' => $saved ? $saved->toArray() : null]);

        $kmSource = 'unknown';
        if ($request->has('kilometer') && $request->filled('kilometer')) {
          $kmSource = 'request';
        } elseif (!empty($existing['kilometer'])) {
          $kmSource = 'existing_detail';
        } else {
          $kmSource = 'default';
        }
        Log::info('simpanDetail: kilometer origin', ['kilometer_value' => $detailData['kilometer'] ?? null, 'source' => $kmSource]);
      } catch (\Exception $e) {}

      // Handle foto deletion
      if ($request->has("foto_delete") && is_array($request->foto_delete)) {
        foreach ($request->foto_delete as $fotoId) {
          $fotoKondisi = FotoKondisiPemakaian::find($fotoId);
          if ($fotoKondisi) {
            // Delete file from storage
            $path = str_replace(asset(""), "", $fotoKondisi->foto_sebelum);
            if (file_exists(public_path($path))) {
              unlink(public_path($path));
            }
            $fotoKondisi->delete();
          }
        }
      }

      // Upload atau update foto kondisi
      // If request contains 'complete_mode', we store uploaded files to foto_sesudah column
      $completeMode = $request->input('complete_mode') == '1' || $request->input('complete_mode') === 'true' || $request->query('complete') == '1';

      if ($request->has("foto") && is_array($request->foto)) {
        foreach ($request->foto as $f) {
          if (is_array($f) && !empty($f["file"])) {
            $file = $f["file"];
            $nip = $user->nip ?? $user->id;
            $posisi = $f["posisi"] ?? "tidakdiketahui";
            $extension = $file->getClientOriginalExtension();
            $filename =
              "{$nip}_{$mobil->id}_{$posisi}_" . time() . ".{$extension}";

            $folder = "uploads/pemakaian";
            $file->move(public_path($folder), $filename);
            $fileUrl = "/{$folder}/{$filename}";

            // Jika ID ada, update foto existing; jika tidak, create baru
            if (!empty($f["id"])) {
              $fotoLama = FotoKondisiPemakaian::find($f["id"]);
              if ($fotoLama) {
                if ($completeMode) {
                  // remove old sesudah if exists
                  $pathLama = preg_replace('#^https?://[^/]+#', '', $fotoLama->foto_sesudah ?? '');
                  if (!empty($pathLama) && file_exists(public_path($pathLama))) {
                    @unlink(public_path($pathLama));
                  }
                  $fotoLama->update([
                    "foto_sesudah" => $fileUrl,
                  ]);
                } else {
                  // delete old sebelum and update
                  $pathLama = preg_replace('#^https?://[^/]+#', '', $fotoLama->foto_sebelum ?? '');
                  if (!empty($pathLama) && file_exists(public_path($pathLama))) {
                    @unlink(public_path($pathLama));
                  }
                  $fotoLama->update([
                    "foto_sebelum" => $fileUrl,
                  ]);
                }
              }
            } else {
              // Create new foto record
              if ($completeMode) {
                FotoKondisiPemakaian::create([
                  "pemakaian_id" => $pemakaian->id,
                  "posisi" => $posisi,
                  "foto_sesudah" => $fileUrl,
                ]);
              } else {
                FotoKondisiPemakaian::create([
                  "pemakaian_id" => $pemakaian->id,
                  "posisi" => $posisi,
                  "foto_sebelum" => $fileUrl,
                ]);
              }
            }
          }
        }
      }

      // If this is a completion submission, save a snapshot of the submitted detail as 'detail_sesudah'
      if ($completeMode) {
        try {
          // use the processed $detailData to ensure all keys exist
          $pemakaian->detail_sesudah = $detailData;
          $pemakaian->save();
        } catch (\Exception $e) {
          // ignore
        }
      }

          // Upload atau update foto SIM (wajib melalui kamera pada UI)
          if ($request->hasFile('sim_foto')) {
            $file = $request->file('sim_foto');
            $nip = $user->nip ?? $user->id;
            $extension = $file->getClientOriginalExtension();
            $filename = "{$nip}_sim_{$mobil->id}_" . time() . ".{$extension}";

            $folder = "uploads/sim";
            $file->move(public_path($folder), $filename);
            $fileUrl = "/{$folder}/{$filename}";

            // If updating, delete old sim file
            if (!empty($pemakaian->sim_foto)) {
              $oldPath = preg_replace('#^https?://[^/]+#', '', $pemakaian->sim_foto);
              if (file_exists(public_path($oldPath))) {
                unlink(public_path($oldPath));
              }
            }

            // keep main sim_foto pointer to latest upload for backward compatibility
            $pemakaian->sim_foto = $fileUrl;

            // Do NOT overwrite snapshot 'sebelum' once set. Save sim_foto as main pointer,
            // and if there's no prior snapshot for sim, capture it once (first upload).
            try {
              $sebelum = $pemakaian->detail_sebelum ?? [];
              if (empty($sebelum['sim_foto'])) {
                $sebelum['sim_foto'] = $fileUrl;
                $pemakaian->detail_sebelum = $sebelum;
              }
            } catch (\Exception $e) {
              // ignore
            }

            $pemakaian->save();
          }
    });

    session()->forget("pemilihan_mobil_id");

    // Send notification to admins for ANY change
    $this->notifyAdminOfChange($pemakaian, $mobil, $isUpdate, $user);

    // Log activity
    try {
      $activityData = [
        "mobil_id" => $mobil->id,
        "tujuan" => $pemakaian->tujuan,
        "tanggal_mulai" => $pemakaian->tanggal_mulai,
        "tanggal_selesai" => $pemakaian->tanggal_selesai,
        "status" => $pemakaian->status,
      ];
      PemakaianActivity::create([
        "pemakaian_id" => $pemakaian->id,
        "user_id" => $user->id,
        "action" => $isUpdate ? "updated" : "created",
        "data" => $activityData,
      ]);
    } catch (\Exception $e) {
    }

    return redirect()
      ->route("pemakaian.daftar")
      ->with(
        "success",
        $isUpdate
          ? "Pemakaian berhasil diupdate."
          : "Pemakaian berhasil dibuat. Menunggu approval admin."
      );
  }

  // Helper method to send notifications to admins
  private function notifyAdminOfChange(
    $pemakaian,
    $mobil,
    $isUpdate = false,
    $user = null
  ) {
    try {
      $pending = PemakaianMobil::where("status", "pending")->count();
      $action = $isUpdate ? "diperbarui" : "baru";

      $payload = [
        "title" => "📋 Pemakaian Mobil " . ucfirst($action),
        "body" => "Mobil {$mobil->no_polisi} - Tujuan: {$pemakaian->tujuan}",
        "details" => $isUpdate
          ? "Ada pembaruan data pemakaian."
          : "Ada pemakaian baru menunggu approval.",
        "pending_count" => $pending,
        "url" => url("/admin/pemakaian"),
        "tag" => "pemakaian-notif",
        "sound" => true,
      ];

      // Send push notification
      SendPushNotification::dispatch($payload);

      // Store notification in database for admins
      $admins = User::where("role", "admin")->get();
      if ($admins->isNotEmpty()) {
        Notification::send($admins, new PemakaianNotification($payload));
      }
    } catch (\Exception $e) {
    }
  }

  // Simpan pilihan mobil ke session
  public function simpanPilihanMobil(Request $request)
  {
    $request->validate([
      "mobil_id" => "required|exists:mobil,id",
    ]);

    $mobil = Mobil::with('detail')->find($request->mobil_id);
    $user = Auth::user();

    if (!$mobil) {
      return redirect()->back()->withErrors('Mobil tidak ditemukan');
    }

    // Ensure mobil is in same penempatan and not deleted
    if ($mobil->penempatan_id != $user->penempatan_id || $mobil->is_deleted) {
      return redirect()->back()->withErrors('Mobil tidak tersedia untuk penempatan Anda');
    }

    // Ensure mobil is not active (already in use)
    $inUse = $mobil->pemakaian()->aktif()->exists();
    if ($inUse) {
      return redirect()->back()->withErrors('Mobil sedang dipakai');
    }

    // Ensure mobil not marked rusak in detail
    if ($mobil->detail && strtolower($mobil->detail->kondisi) === 'rusak') {
      return redirect()->back()->withErrors('Mobil sedang rusak');
    }

    // If current time is full window, ensure this mobil is not already assigned to another user
    $nowJkt = Carbon::now('Asia/Jakarta');
    $mNow = ((int)$nowJkt->format('H')) * 60 + (int)$nowJkt->format('i');
    $isFullNow = ($mNow >= 360 && $mNow < 600) || ($mNow >= 990 && $mNow < 1200);
    if ($isFullNow) {
      $assignedOther = User::whereNotNull('mobil_id')
        ->where('mobil_id', $mobil->id)
        ->where('id', '<>', $user->id)
        ->exists();
      if ($assignedOther) {
        return redirect()->back()->withErrors('Mobil sudah menjadi mobil pegangan driver lain. Pilih mobil lain.');
      }
    }

    // Save selection
  session(["pemilihan_mobil_id" => $request->mobil_id]);

  return redirect()->route("pemakaian.inputDetail");
  }

  /**
   * Start completion flow for a pemakaian: ensure ownership, set session mobil and
   * redirect to inputDetail in completion mode where user can submit 'sesudah' photos.
   */
  public function startCompletion(Request $request, $id)
  {
    $user = Auth::user();

    $pemakaian = PemakaianMobil::with('mobil')->findOrFail($id);

    // Only owner can complete their pemakaian (admins allowed)
    if (($user->role ?? '') !== 'admin' && $pemakaian->user_id !== $user->id) {
      return redirect()->back()->withErrors('Anda tidak memiliki izin menyelesaikan pemakaian ini.');
    }

    // Set session for inputDetail to pick mobil
    session(['pemilihan_mobil_id' => $pemakaian->mobil_id]);

    // Redirect to input form in completion mode (use edit_id so controller will load $pemakaian)
    return redirect()->route('pemakaian.inputDetail', ['edit_id' => $pemakaian->id, 'complete' => 1]);
  }

  // Daftar semua pemakaian user dengan search dan pagination
  public function daftar(Request $request)
  {
    $user = Auth::user();
    $search = $request->input("search", "");
    $status = $request->input("status", "");
    $date_from = $request->input('date_from', '');
    $date_to = $request->input('date_to', '');

    $query = PemakaianMobil::with([
      "mobil.merek",
      "detail",
      "fotoKondisiPemakaian",
      "user",
    ])->where("user_id", $user->id);

    // Search berdasarkan tujuan
    if (!empty($search)) {
      $query->where("tujuan", "like", "%" . $search . "%");
    }

    // Filter berdasarkan status
    if (!empty($status)) {
      $query->where("status", $status);
    }

    // Filter berdasarkan tanggal mulai (range)
    if (!empty($date_from)) {
      $query->whereDate('tanggal_mulai', '>=', $date_from);
    }

    if (!empty($date_to)) {
      $query->whereDate('tanggal_mulai', '<=', $date_to);
    }

    // Pagination 10 per halaman
    $pemakaian = $query->orderBy("created_at", "desc")->paginate(10);

    return view("pemakaian.daftar", compact("pemakaian", "search", "status", 'date_from', 'date_to'));
  }

  // Detail pemakaian (untuk modal)
  public function detail($id)
  {
    $user = Auth::user();

    $pemakaian = PemakaianMobil::with([
      "mobil.merek",
      "detail",
      "fotoKondisiPemakaian",
    ])
      ->where("user_id", $user->id)
      ->findOrFail($id);

    $detail = $pemakaian->detail ? $pemakaian->detail->toArray() : [];
    $foto = $pemakaian->fotoKondisiPemakaian
      ? $pemakaian->fotoKondisiPemakaian->toArray()
      : [];

    return response()->json([
      "id" => $pemakaian->id,
      "sim_foto" => $pemakaian->sim_foto ?? null,
      "mobil" => [
        "no_polisi" => $pemakaian->mobil->no_polisi,
        "merek" => ["nama_merek" => $pemakaian->mobil->merek->nama_merek ?? ""],
      ],
      "tujuan" => $pemakaian->tujuan,
      "tanggal_mulai" => $pemakaian->tanggal_mulai,
      "tanggal_selesai" => $pemakaian->tanggal_selesai,
      "jarak_tempuh_km" => $pemakaian->jarak_tempuh_km,
      "bahan_bakar" => $pemakaian->bahan_bakar ?? "-",
      "bahan_bakar_liter" => $pemakaian->bahan_bakar_liter ?? "-",
      "alasan_reject" => $pemakaian->alasan_reject ?? null,
      "transmisi" => $pemakaian->transmisi ?? "-",
      "catatan" => $pemakaian->catatan ?? "-",
      "status" => $pemakaian->status,
      // include mobil's current detail for compatibility
      "detail" => $detail,
      // include snapshot fields to allow before/after comparison
      "detail_sebelum" => $pemakaian->detail_sebelum ?? [],
      "detail_sesudah" => $pemakaian->detail_sesudah ?? [],
      "foto_kondisi" => $foto,
    ]);
  }

  /**
   * Hapus pemakaian beserta foto terkait.
   * - Admin bisa menghapus semua.
   * - Pegawai hanya bisa menghapus pemakaian miliknya jika status = pending.
   */
  public function destroy(Request $request, $id)
  {
    $user = Auth::user();
    $pemakaian = PemakaianMobil::with([
      "fotoKondisiPemakaian",
      "user",
    ])->findOrFail($id);

    // Authorization
    if ($user->role !== "admin") {
      // pegawai hanya boleh hapus sendiri bila pending
      if (
        $pemakaian->user_id !== $user->id ||
        $pemakaian->status !== "pending"
      ) {
        if ($request->ajax()) {
          return response()->json(
            [
              "success" => false,
              "message" => "Anda tidak memiliki izin untuk menghapus data ini.",
            ],
            403
          );
        }
        return redirect()
          ->back()
          ->withErrors("Anda tidak memiliki izin untuk menghapus data ini.");
      }
    }

    // Delete foto files and records
    foreach ($pemakaian->fotoKondisiPemakaian as $foto) {
      foreach (["foto_sebelum", "foto_sesudah"] as $col) {
        if (!empty($foto->$col)) {
          // stored as full URL via asset(), remove domain part
          $path = str_replace(asset(""), "", $foto->$col);
          $full = public_path($path);
          if (file_exists($full)) {
            try {
              unlink($full);
            } catch (\Exception $e) {
            }
          }
        }
      }
      try {
        $foto->delete();
      } catch (\Exception $e) {
      }
    }

    // Delete pemakaian record
    try {
      $pemakaian->delete();
    } catch (\Exception $e) {
      if ($request->ajax()) {
        return response()->json(
          ["success" => false, "message" => "Gagal menghapus data."],
          500
        );
      }
      return redirect()
        ->back()
        ->withErrors("Gagal menghapus data.");
    }

    // Log activity
    try {
      PemakaianActivity::create([
        "pemakaian_id" => $pemakaian->id,
        "user_id" => $user->id,
        "action" => "deleted",
        "data" => ["deleted_by" => $user->id],
      ]);
    } catch (\Exception $e) {
    }

    if ($request->ajax()) {
      return response()->json([
        "success" => true,
        "message" => "Pemakaian berhasil dihapus.",
      ]);
    }

    return redirect()
      ->route("pemakaian.daftar")
      ->with("success", "Pemakaian berhasil dihapus.");
  }

  public function daftarMobil()
{
    $user = Auth::user();

    $mobilRusak = Mobil::with(['merek', 'penempatan', 'detail'])
        ->where('penempatan_id', $user->penempatan_id)
      ->whereNull('is_deleted')
        ->get();

    return view('mobil.lapor-rusak', ['mobilRusak' => $mobilRusak, 'mobil' => null]);
}
  
  // Tampilkan form lapor rusak
  public function showLaporRusakForm($mobilId)
  {
    $mobil = Mobil::with(['merek', 'jenis', 'penempatan'])->findOrFail($mobilId);
    $user = Auth::user();
    
    // Tentukan view berdasarkan role
    $view = $user->role === 'pegawai' ? 'pegawai.lapor-rusak' : 'mobil.lapor-rusak';
    
    // Ambil semua mobil pada penempatan yang sama untuk dropdown
    $mobilRusak = Mobil::with(['merek'])
        ->where('penempatan_id', $user->penempatan_id)
      ->whereNull('is_deleted')
      ->whereDoesntHave('detail', function($q){
        $q->where('kondisi', 'rusak');
      })
        ->get();

    return view($view, compact('mobil', 'mobilRusak'));
  }

  /**
   * Lapor kondisi rusak mobil dengan dokumentasi foto
   */
  public function laporRusak(Request $request)
  {
    // Validasi input
    $request->validate([
      'mobil_id' => 'required|exists:mobil,id',
      'kategori' => 'nullable|string',
      'kondisi' => 'nullable|in:Rusak Ringan,Rusak Sedang,Rusak Berat',
      'foto.*.posisi' => 'required_with:foto.*.file|in:depan,belakang,kanan,kiri,interior,lainnya,joksabuk,acventilasi,panelaudio,lampukabin,interior_bersih,toolkitdongkrak',
      'foto.*.file' => 'nullable|image|max:61440',
      'sim_foto' => 'nullable|image|max:61440',
      'stnk_foto' => 'nullable|image|max:61440',
      'odo_meter' => 'nullable|image|max:61440',
      'kir_photos.*' => 'nullable|image|max:61440',
    ], [
      'mobil_id.required' => 'ID Mobil harus ada',
      'foto.*.file.image' => 'File harus berupa gambar',
      'foto.*.file.max' => 'Ukuran file maksimal 60MB',
    ]);

    $mobil = Mobil::findOrFail($request->mobil_id);
    $user = Auth::user();

    // Pastikan ada nilai kondisi; default ke 'Rusak Ringan' jika tidak dikirim
    $kondisi = $request->input('kondisi', 'Rusak Ringan');

    // Update atau buat detail kondisi rusak
    $detail = $mobil->detail ?? new DetailMobil();
    $detail->mobil_id = $mobil->id;
    
    // Update field kondisi dari request jika ada
    $fields = ['depan', 'belakang', 'kanan', 'kiri', 'joksabuk', 'acventilasi', 
               'panelaudio', 'lampukabin', 'interior_bersih', 'toolkitdongkrak'];
    
    foreach ($fields as $field) {
      if ($request->filled($field)) {
        $detail->$field = $request->$field;
      }
    }
    
    $detail->kondisi = 'rusak';
    $detail->save();

    // Buat record laporan rusak yang akan menampung foto dan informasi laporan
    // Prevent duplicate pending laporan for the same mobil
    if (LaporanRusak::where('mobil_id', $mobil->id)->where('status', LaporanRusak::STATUS_PENDING)->exists()) {
      return redirect()->back()->withErrors('Mobil ini sudah memiliki laporan kerusakan yang sedang diproses.');
    }

    try {
      $laporan = LaporanRusak::create([
        'user_id' => $user->id,
        'mobil_id' => $mobil->id,
        'kategori' => $request->input('kategori') ?? null,
        'kondisi' => $kondisi,
        'catatan' => $request->catatan ?? null,
        'lokasi' => $request->lokasi ?? null,
        // 'odo_meter' will be saved after file upload handling
        'status' => LaporanRusak::STATUS_PENDING,
      ]);
    } catch (\Exception $e) {
      $laporan = null;
    }

    // Tandai mobil agar tidak bisa dipilih/dipakai sementara
    try {
      $mobil->is_deleted = 1;
      $mobil->save();
    } catch (\Exception $e) {
      // silent fail
    }

    // Upload foto kerusakan
    // Upload foto kerusakan
    // Handle legacy single input 'foto_bukti' from the form
    if ($request->hasFile('foto_bukti')) {
      $file = $request->file('foto_bukti');
      $f = ['file' => $file, 'posisi' => $request->input('posisi', 'lainnya')];
      $this->uploadFoto($f, $mobil, $user, $laporan);
    }

    // Also handle array input 'foto' (existing behavior)
    if ($request->has('foto') && is_array($request->foto)) {
      foreach ($request->foto as $f) {
        if (is_array($f) && !empty($f['file'])) {
          $this->uploadFoto($f, $mobil, $user, $laporan);
        }
      }
    }

    // Handle sim_foto (must be from camera UI on client side)
    if ($request->hasFile('sim_foto') && $laporan) {
      $file = $request->file('sim_foto');
      $nip = $user->nip ?? $user->id;
      $extension = $file->getClientOriginalExtension();
      $filename = "{$nip}_sim_{$mobil->id}_" . time() . ".{$extension}";
      $folder = 'uploads/lapor_rusak_sim';
      $this->compressAndMove($file, $folder, $filename);
      $laporan->sim_foto = "/{$folder}/{$filename}";
      $laporan->save();
    }

    // Handle stnk_foto
    if ($request->hasFile('stnk_foto') && $laporan) {
      $file = $request->file('stnk_foto');
      $nip = $user->nip ?? $user->id;
      $extension = $file->getClientOriginalExtension();
      $filename = "{$nip}_stnk_{$mobil->id}_" . time() . ".{$extension}";
      $folder = 'uploads/lapor_rusak_stnk';
      $this->compressAndMove($file, $folder, $filename);
      $laporan->stnk_foto = "/{$folder}/{$filename}";
      $laporan->save();
    }

    // Handle odo_meter as photo
    if ($request->hasFile('odo_meter') && $laporan) {
      $file = $request->file('odo_meter');
      $nip = $user->nip ?? $user->id;
      $extension = $file->getClientOriginalExtension();
      $filename = "{$nip}_odo_{$mobil->id}_" . time() . ".{$extension}";
      $folder = 'uploads/lapor_rusak_odo';
      $this->compressAndMove($file, $folder, $filename);
      $laporan->odo_meter = "/{$folder}/{$filename}";
      $laporan->save();
    }

    // If kategori Pengajuan KIR, expect kir_photos array with keys kiri, kanan, depan, belakang
    if (($request->input('kategori') === 'Pengajuan KIR') && $laporan) {
      $kir = $request->input('kir_photos', []);
      // kir_photos may be provided as an array of files
      if ($request->hasFile('kir_photos')) {
        $files = $request->file('kir_photos');
        foreach ($files as $key => $file) {
          if (!$file) continue;
          $posisi = in_array($key, ['kiri','kanan','depan','belakang']) ? $key : 'kir_' . $key;
          $this->uploadFoto(['file' => $file, 'posisi' => $posisi, 'laporan' => $laporan], $mobil, $user, $laporan);
        }
      }
    }

    // Log aktivitas
    try {
      PemakaianActivity::create([
        "user_id" => $user->id,
        "action" => "lapor_rusak",
        "data" => [
          "mobil_id" => $mobil->id,
          "kondisi_status" => $kondisi,
          "catatan" => $request->catatan ?? null,
        ],
      ]);
    } catch (\Exception $e) {
      // Silent fail - log saja
    }

    return redirect()
      ->back()
      ->with("success", "Kondisi rusak mobil {$mobil->no_polisi} berhasil dilaporkan.");
  }

  /**
   * Helper: Upload foto kerusakan
   */
  private function uploadFoto($fotoData, $mobil, $user, $laporan = null)
  {
    try {
      if ($laporan) {
        $fotoData['laporan'] = $laporan;
      }
      $file = $fotoData['file'];
      $nip = $user->nip ?? $user->id;
      $posisi = $fotoData['posisi'] ?? 'lainnya';
      $extension = $file->getClientOriginalExtension();
      $filename = "{$nip}_{$mobil->id}_rusak_{$posisi}_" . time() . ".{$extension}";

      $folder = 'uploads/lapor_rusak';
      // compress and move to destination, store relative path so host differences don't matter
      $this->compressAndMove($file, $folder, $filename);
      $fileUrl = "/{$folder}/{$filename}";

      // Simpan record foto ke tabel laporan rusak jika tersedia,
      // fallback ke model foto kondisi pemakaian jika tidak ada laporan
      if (isset($fotoData['laporan']) && $fotoData['laporan'] instanceof LaporanRusak) {
        LaporanRusakFoto::create([
          'laporan_rusak_id' => $fotoData['laporan']->id,
          'posisi' => $posisi,
          'file_path' => $fileUrl,
        ]);
      } elseif (isset($fotoData['laporan_id']) && $fotoData['laporan_id']) {
        LaporanRusakFoto::create([
          'laporan_rusak_id' => $fotoData['laporan_id'],
          'posisi' => $posisi,
          'file_path' => $fileUrl,
        ]);
      } elseif (isset($fotoData['laporan']) && is_numeric($fotoData['laporan'])) {
        LaporanRusakFoto::create([
          'laporan_rusak_id' => $fotoData['laporan'],
          'posisi' => $posisi,
          'file_path' => $fileUrl,
        ]);
      } else {
        FotoKondisiPemakaian::create([
          'pemakaian_id' => null,
          'posisi' => $posisi,
          'foto_sebelum' => $fileUrl,
        ]);
      }
    } catch (\Exception $e) {
      // Silent fail
    }
  }

  /**
   * Compress uploaded image and move to public folder.
   * Tries to use GD to re-encode and reduce size while keeping reasonable quality.
   */
  private function compressAndMove($file, $folder, $filename)
  {
    try {
      $destDir = public_path($folder);
      if (!file_exists($destDir)) {
        @mkdir($destDir, 0755, true);
      }

      $destPath = $destDir . DIRECTORY_SEPARATOR . $filename;

      // Attempt to create image resource
      $contents = file_get_contents($file->getRealPath());
      if ($contents === false) {
        // fallback to move
        $file->move($destDir, $filename);
        return;
      }

      $img = @imagecreatefromstring($contents);
      if ($img === false) {
        // binary move fallback
        $file->move($destDir, $filename);
        return;
      }

      $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
      if (in_array($ext, ['jpg', 'jpeg'])) {
        imagejpeg($img, $destPath, 85);
      } elseif ($ext === 'png') {
        // PNG quality param: 0 (no compression) - 9
        imagepng($img, $destPath, 6);
      } else {
        // default to jpeg for other formats
        imagejpeg($img, $destPath, 85);
      }

      imagedestroy($img);
    } catch (\Exception $e) {
      try {
        $file->move(public_path($folder), $filename);
      } catch (\Exception $e) {
        // ignore
      }
    }
  }
}

