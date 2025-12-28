<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\JenisMobil;
use App\Models\MerekMobil;
use App\Models\Mobil;
use App\Models\Penempatan;

// Note: Requires phpoffice/phpspreadsheet package. Run:
// composer require phpoffice/phpspreadsheet

class ExportImportController extends Controller
{
    protected $allowed = ['user','jenis','merek','mobil','penempatan'];

    public function index()
    {
        return view('admin.tools.import_export');
    }

    public function downloadTemplate($model)
    {
        if (!in_array($model, $this->allowed)) abort(404);

        // Lazy load PhpSpreadsheet here to avoid fatal if not installed
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            return redirect()->back()->with('error', 'PhpSpreadsheet tidak terpasang. Jalankan composer require phpoffice/phpspreadsheet');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($model === 'user') {
            $headers = ['id','name','username','email','nip','role','password'];
            $sheet->fromArray($headers, null, 'A1');
        } elseif ($model === 'jenis') {
            $headers = ['id','nama_jenis','keterangan'];
            $sheet->fromArray($headers, null, 'A1');
        } elseif ($model === 'merek') {
            $headers = ['id','nama_merek'];
            $sheet->fromArray($headers, null, 'A1');
        } elseif ($model === 'mobil') {
            $headers = ['id','no_polisi','merek_id','jenis_id','tipe','tahun','penempatan_id'];
            $sheet->fromArray($headers, null, 'A1');
        } elseif ($model === 'penempatan') {
            $headers = ['id','kode_kantor','nama_kantor','alamat','kota','provinsi'];
            $sheet->fromArray($headers, null, 'A1');
        }

        // Styling header bold
        $sheet->getStyle('A1:Z1')->getFont()->setBold(true);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = "template_{$model}.xlsx";

        // Stream to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    public function export(Request $request)
    {
        $model = $request->input('model');
        if (!in_array($model, $this->allowed)) return back()->with('error', 'Model tidak valid.');

        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            return back()->with('error', 'PhpSpreadsheet tidak terpasang. Jalankan composer require phpoffice/phpspreadsheet');
        }

        $ids = $request->input('ids', []);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($model === 'user') {
            $rows = User::when($ids, function($q) use ($ids){ return $q->whereIn('id', $ids); })->get();
            $headers = ['id','name','username','email','nip','role','created_at'];
            $sheet->fromArray($headers, null, 'A1');
            $r = 2;
            foreach ($rows as $row) {
                $sheet->setCellValue("A{$r}", $row->id);
                $sheet->setCellValue("B{$r}", $row->name);
                $sheet->setCellValue("C{$r}", $row->username);
                $sheet->setCellValue("D{$r}", $row->email);
                $sheet->setCellValue("E{$r}", $row->nip);
                $sheet->setCellValue("F{$r}", $row->role);
                $sheet->setCellValue("G{$r}", $row->created_at);
                $r++;
            }
            $fileName = 'users_export_' . date('Ymd_His') . '.xlsx';
        } elseif ($model === 'jenis') {
            $rows = JenisMobil::when($ids, function($q) use ($ids){ return $q->whereIn('id', $ids); })->get();
            $headers = ['id','nama_jenis','keterangan','created_at'];
            $sheet->fromArray($headers, null, 'A1'); $r=2;
            foreach ($rows as $row) {
                $sheet->fromArray([$row->id,$row->nama_jenis,$row->keterangan,$row->created_at], null, "A{$r}"); $r++;
            }
            $fileName = 'jenis_mobil_export_' . date('Ymd_His') . '.xlsx';
        } elseif ($model === 'merek') {
            $rows = MerekMobil::when($ids, function($q) use ($ids){ return $q->whereIn('id', $ids); })->get();
            $headers = ['id','nama_merek','created_at'];
            $sheet->fromArray($headers, null, 'A1'); $r=2;
            foreach ($rows as $row) { $sheet->fromArray([$row->id,$row->nama_merek,$row->created_at], null, "A{$r}"); $r++; }
            $fileName = 'merek_mobil_export_' . date('Ymd_His') . '.xlsx';
        } elseif ($model === 'mobil') {
            $rows = Mobil::with('merek','jenis')->when($ids, function($q) use ($ids){ return $q->whereIn('id', $ids); })->get();
            $headers = ['id','no_polisi','merek','jenis','tipe','tahun','penempatan_id','created_at'];
            $sheet->fromArray($headers, null, 'A1'); $r=2;
            foreach ($rows as $row) {
                $sheet->fromArray([$row->id,$row->no_polisi,$row->merek->nama_merek ?? '',$row->jenis->nama_jenis ?? '',$row->tipe,$row->tahun,$row->penempatan_id,$row->created_at], null, "A{$r}"); $r++; }
            $fileName = 'mobil_export_' . date('Ymd_His') . '.xlsx';
        } else { // penempatan
            $rows = Penempatan::when($ids, function($q) use ($ids){ return $q->whereIn('id', $ids); })->get();
            $headers = ['id','kode_kantor','nama_kantor','alamat','kota','provinsi','created_at'];
            $sheet->fromArray($headers, null, 'A1'); $r=2;
            foreach ($rows as $row) {
                $sheet->fromArray([$row->id,$row->kode_kantor,$row->nama_kantor,$row->alamat,$row->kota,$row->provinsi,$row->created_at], null, "A{$r}"); $r++; }
            $fileName = 'penempatan_export_' . date('Ymd_His') . '.xlsx';
        }

        // Auto-size columns A..Z (simple loop)
        foreach (range('A','Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $model = $request->input('model');
        if (!in_array($model, $this->allowed)) return back()->with('error', 'Model tidak valid.');
        if (!$request->hasFile('file')) return back()->with('error', 'File tidak ditemukan.');

        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            return back()->with('error', 'PhpSpreadsheet tidak terpasang. Jalankan composer require phpoffice/phpspreadsheet');
        }

        $file = $request->file('file')->getPathname();
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Expect header in first row
        if (count($rows) < 2) return back()->with('error', 'File kosong atau tidak ada data.');

        $header = array_map('strtolower', array_values($rows[1]));
        $inserted = 0; $updated = 0; $errors = [];

        DB::beginTransaction();
        try {
            for ($i = 2; $i <= count($rows); $i++) {
                $r = $rows[$i];
                $data = [];
                // Map by header names A,B,C... convert to assoc by header
                $cols = array_values($r);
                for ($j=0;$j<count($header);$j++) {
                    $key = $header[$j] ?? null;
                    if (!$key) continue;
                    $data[$key] = $cols[$j] ?? null;
                }

                if ($model === 'user') {
                    if (empty($data['email']) && empty($data['username'])) { $errors[] = "Baris {$i}: email/username kosong"; continue; }
                    $user = null;
                    if (!empty($data['id'])) $user = User::find($data['id']);
                    if (!$user && !empty($data['email'])) $user = User::where('email', $data['email'])->first();
                    if (!$user && !empty($data['username'])) $user = User::where('username', $data['username'])->first();

                    $payload = [
                        'name' => $data['name'] ?? null,
                        'username' => $data['username'] ?? null,
                        'email' => $data['email'] ?? null,
                        'nip' => $data['nip'] ?? null,
                        'role' => $data['role'] ?? 'pegawai'
                    ];
                    if (!empty($data['password'])) $payload['password'] = Hash::make($data['password']);

                    if ($user) { $user->update($payload); $updated++; }
                    else { User::create(array_merge($payload, ['password' => $payload['password'] ?? Hash::make(Str::random(8))])); $inserted++; }
                } elseif ($model === 'jenis') {
                    if (empty($data['nama_jenis'])) { $errors[] = "Baris {$i}: nama_jenis kosong"; continue; }
                    $jenis = null; if (!empty($data['id'])) $jenis = JenisMobil::find($data['id']);
                    if ($jenis) { $jenis->update(['nama_jenis'=>$data['nama_jenis'],'keterangan'=>$data['keterangan'] ?? null]); $updated++; }
                    else { JenisMobil::create(['nama_jenis'=>$data['nama_jenis'],'keterangan'=>$data['keterangan'] ?? null]); $inserted++; }
                } elseif ($model === 'merek') {
                    if (empty($data['nama_merek'])) { $errors[] = "Baris {$i}: nama_merek kosong"; continue; }
                    $merek = null; if (!empty($data['id'])) $merek = MerekMobil::find($data['id']);
                    if ($merek) { $merek->update(['nama_merek'=>$data['nama_merek']]); $updated++; }
                    else { MerekMobil::create(['nama_merek'=>$data['nama_merek']]); $inserted++; }
                } elseif ($model === 'mobil') {
                    if (empty($data['no_polisi'])) { $errors[] = "Baris {$i}: no_polisi kosong"; continue; }
                    $mobil = null; if (!empty($data['id'])) $mobil = Mobil::find($data['id']);
                    $payload = [
                        'no_polisi' => $data['no_polisi'],
                        'merek_id' => $data['merek_id'] ?: null,
                        'jenis_id' => $data['jenis_id'] ?: null,
                        'tipe' => $data['tipe'] ?? null,
                        'tahun' => $data['tahun'] ?? null,
                        'penempatan_id' => $data['penempatan_id'] ?: null,
                    ];
                    if ($mobil) { $mobil->update($payload); $updated++; }
                    else { Mobil::create($payload); $inserted++; }
                } else { // penempatan
                    if (empty($data['kode_kantor'])) { $errors[] = "Baris {$i}: kode_kantor kosong"; continue; }
                    $penempatan = null; if (!empty($data['id'])) $penempatan = Penempatan::find($data['id']);
                    $payload = [
                        'kode_kantor' => $data['kode_kantor'],
                        'nama_kantor' => $data['nama_kantor'] ?? null,
                        'alamat' => $data['alamat'] ?? null,
                        'kota' => $data['kota'] ?? null,
                        'provinsi' => $data['provinsi'] ?? null,
                    ];
                    if ($penempatan) { $penempatan->update($payload); $updated++; }
                    else { Penempatan::create($payload); $inserted++; }
                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $ex->getMessage());
        }

        $msg = "Import selesai. Ditambahkan: {$inserted}, Diperbarui: {$updated}.";
        if ($errors) $msg .= ' Beberapa baris dilewati: ' . implode('; ', array_slice($errors,0,5));
        return back()->with('success', $msg);
    }
}
