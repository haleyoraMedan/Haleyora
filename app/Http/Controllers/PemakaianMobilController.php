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

class PemakaianMobilController extends Controller
{
  // Menampilkan daftar mobil yang tersedia untuk dipilih
  public function pilihMobil()
{
    $user = Auth::user();

    $mobils = Mobil::with(['merek', 'penempatan'])
        ->where('penempatan_id', $user->penempatan_id)

        // ❌ mobil yang sedang dipakai (aktif) tidak ditampilkan
        ->whereDoesntHave('pemakaian', function ($q) {
            $q->aktif();
        })

        // ❌ mobil rusak tidak ditampilkan
      ->whereDoesntHave('detail', function ($q) {
        $q->where('kondisi', 'rusak');
      })
      ->whereNull('is_deleted')

        ->get();

    $pilihanMobilId = session('pemilihan_mobil_id');

    return view('pemakaian.pilih_mobil', compact('mobils', 'pilihanMobilId'));
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
      ])
        ->where("id", $request->edit_id)
        ->where("status", "pending") // hanya bisa edit yg pending
        ->firstOrFail();

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

    // Determine restriction based on Jakarta time: after or equal 10:00 is restricted
    $nowJkt = Carbon::now('Asia/Jakarta');
    $is_restricted = $nowJkt->hour >= 10;
    $current_time_jkt = $nowJkt->format('H:i');

    return view("pemakaian.input_detail", compact("mobil", "pemakaian", "is_restricted", "current_time_jkt"));
  }

  // Simpan atau update detail pemakaian
  public function simpanDetail(Request $request, $id = null)
  {
    $user = Auth::user();

    // Time-based restriction (Asia/Jakarta). If time >= 10:00, limit inputs.
    $is_restricted = Carbon::now('Asia/Jakarta')->hour >= 10;

    // Determine which pemakaian we're working with
    if ($id) {
      // Edit mode - get from ID
      $pemakaian = PemakaianMobil::where("id", $id)
        ->where("status", "pending")
        ->firstOrFail();
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
    $rules = [
      "tujuan" => "required|string|max:255",
      "tanggal_mulai" => "required|date",
      "tanggal_selesai" => "nullable|date|after_or_equal:tanggal_mulai",
      "kilometer" => $is_restricted ? "nullable|integer" : "required|integer",
      "jarak_tempuh_km" => "nullable|numeric",
      "bahan_bakar" => "required|in:Bensin,Solar,Listrik",
      "bahan_bakar_liter" => "nullable|numeric",
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
      "foto.*.file" => "nullable|image|max:2048",
    ];

    $messages = [
      "foto.*.posisi.required_with" => "Posisi foto harus diisi jika ada file foto",
      "foto.*.posisi.in" => "Posisi foto harus salah satu dari: depan, belakang, kanan, kiri, joksabuk, acventilasi, panelaudio, lampukabin, interior_bersih, toolkitdongkrak",
    ];

    $request->validate($rules, $messages);

    $isUpdate = (bool) $id;

    DB::transaction(function () use (
      $request,
      $user,
      $mobil,
      $id,
      &$pemakaian,
      $is_restricted
    ) {
      if ($id) {
        // Update existing pemakaian
        $pemakaian->update([
          "tujuan" => $request->tujuan,
          "tanggal_mulai" => $request->tanggal_mulai,
          "tanggal_selesai" => $request->tanggal_selesai,
          "jarak_tempuh_km" => $request->jarak_tempuh_km ?? 0,
          "bahan_bakar_liter" => $request->bahan_bakar_liter ?? 0,
          "catatan" => $request->catatan,
        ]);
      } else {
        // Create new pemakaian
        $pemakaian = PemakaianMobil::create([
          "mobil_id" => $mobil->id,
          "user_id" => $user->id,
          "tujuan" => $request->tujuan,
          "tanggal_mulai" => $request->tanggal_mulai,
          "tanggal_selesai" => $request->tanggal_selesai,
          "jarak_tempuh_km" => $request->jarak_tempuh_km ?? 0,
          "bahan_bakar_liter" => $request->bahan_bakar_liter ?? 0,
          "catatan" => $request->catatan,
          "status" => "approved",
        ]);
      }

      // Update atau buat detail mobil
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

      // If restricted period, ensure condition-related fields are stored as '-' when not provided
      if ($is_restricted) {
        $conditionOnly = [
          'depan','belakang','kanan','kiri','joksabuk','acventilasi','panelaudio','lampukabin','interior_bersih','toolkitdongkrak','kondisi'
        ];
        foreach ($conditionOnly as $f) {
          if (empty($detailData[$f])) {
            $detailData[$f] = '-';
          }
        }
      }

      DetailMobil::updateOrCreate([
        "mobil_id" => $mobil->id
      ], $detailData);

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
      if ($request->has("foto") && is_array($request->foto)) {
        foreach ($request->foto as $f) {
          if (is_array($f) && !empty($f["file"])) {
            $file = $f["file"];
            $nip = $user->nip ?? $user->id;
            $posisi = $f["posisi"] ?? "tidakdiketahui";
            $extension = $file->getClientOriginalExtension();
            $filename =
              "{$nip}_{$mobil->id}_{$posisi}_" . time() . ".{$extension}";

            $folder = "uploads/pemakaian_sebelum";
            $file->move(public_path($folder), $filename);
            $fileUrl = asset("{$folder}/{$filename}");

            // Jika ID ada, update foto existing; jika tidak, create baru
            if (!empty($f["id"])) {
              $fotoLama = FotoKondisiPemakaian::find($f["id"]);
              if ($fotoLama) {
                // Delete old file
                $pathLama = str_replace(asset(""), "", $fotoLama->foto_sebelum);
                if (file_exists(public_path($pathLama))) {
                  unlink(public_path($pathLama));
                }
                // Update dengan file baru
                $fotoLama->update([
                  "foto_sebelum" => $fileUrl,
                ]);
              }
            } else {
              // Create new foto record
              FotoKondisiPemakaian::create([
                "pemakaian_id" => $pemakaian->id,
                "posisi" => $posisi,
                "foto_sebelum" => $fileUrl,
              ]);
            }
          }
        }
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

    session(["pemilihan_mobil_id" => $request->mobil_id]);

    return redirect()->route("pemakaian.inputDetail");
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
      "transmisi" => $pemakaian->transmisi ?? "-",
      "catatan" => $pemakaian->catatan ?? "-",
      "status" => $pemakaian->status,
      "detail" => $detail,
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
      'kondisi' => 'nullable|in:Rusak Ringan,Rusak Sedang,Rusak Berat',
      'foto.*.posisi' => 'required_with:foto.*.file|in:depan,belakang,kanan,kiri,interior,lainnya,joksabuk,acventilasi,panelaudio,lampukabin,interior_bersih,toolkitdongkrak',
      'foto.*.file' => 'nullable|image|max:2048',
    ], [
      'mobil_id.required' => 'ID Mobil harus ada',
      'foto.*.file.image' => 'File harus berupa gambar',
      'foto.*.file.max' => 'Ukuran file maksimal 2MB',
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
    // Prevent duplicate laporan for the same mobil
    if (LaporanRusak::where('mobil_id', $mobil->id)->exists()) {
      return redirect()->back()->withErrors('Mobil ini sudah memiliki laporan kerusakan.');
    }

    try {
      $laporan = LaporanRusak::create([
        'user_id' => $user->id,
        'mobil_id' => $mobil->id,
        'kondisi' => $kondisi,
        'catatan' => $request->catatan ?? null,
        'lokasi' => $request->lokasi ?? null,
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
      $file->move(public_path($folder), $filename);
      $fileUrl = asset("{$folder}/{$filename}");

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
}

