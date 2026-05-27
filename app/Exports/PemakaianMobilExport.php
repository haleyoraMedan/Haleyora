<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class PemakaianMobilExport
{
    protected $pemakaian;

    // 3 foto kolom per baris, tanpa kolom label kiri
    const PHOTO_COLS     = ['A', 'B', 'C'];
    const PHOTOS_PER_ROW = 3;

    // ── Ukuran foto & baris disesuaikan agar muat A4 portrait ────────────
    // A4 portrait usable height ≈ 756 pt (11in - 0.8in margin).
    // Layout: title(28) + date(14) + info_header(16) + 17 info rows(15ea=255)
    //       + foto_header(16) + 4 foto-chunks × (caption(16) + photo(??)) = muat
    // Sisa setelah info section: 756 - 28 - 14 - 16 - 255 - 16 = 427 pt
    // 4 chunks × (16 caption + photo) → max photo row = (427 - 4×16) / 4 = 91 pt
    // Pakai 85 pt untuk ada sedikit margin aman.
    const ROW_INFO_H     = 15;   // info data row height (pt)
    const ROW_CAPTION_H  = 16;   // foto caption row (pt)
    const ROW_PHOTO_H    = 82;   // foto photo row (pt)  ← kunci agar muat A4

    // Foto target pixel (disesuaikan dengan ROW_PHOTO_H)
    // ROW_PHOTO_H pt → px ≈ pt / 0.75 = ~109px; kurangi padding → 100px
    const PHOTO_W        = 130;
    const PHOTO_H        = 100;

    public function __construct($pemakaian)
    {
        $this->pemakaian = $pemakaian;
    }

    public function generate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ── Page setup: A4 portrait, fit ke 1 halaman lebar ──────────────────
        $ps = $sheet->getPageSetup();
        $ps->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $ps->setPaperSize(PageSetup::PAPERSIZE_A4);
        $ps->setHorizontalCentered(true);
        $ps->setFitToPage(true);
        $ps->setFitToWidth(1);
        $ps->setFitToHeight(1); // paksa muat 1 halaman tinggi juga

        $pm = $sheet->getPageMargins();
        $pm->setTop(0.4);
        $pm->setBottom(0.4);
        $pm->setLeft(0.35);
        $pm->setRight(0.35);

        // ── Lebar kolom: 3 kolom sama rata ───────────────────────────────────
        foreach (self::PHOTO_COLS as $col) {
            $sheet->getColumnDimension($col)->setWidth(23);
        }

        // ── Helpers ───────────────────────────────────────────────────────────
        $createTempFromUrl = function ($url) {
            if (!$url) return null;
            try { $base = asset(''); } catch (\Throwable $e) { $base = null; }
            $path = $url;
            if ($base && strpos($url, $base) === 0) {
                $path = str_replace($base, '', $url);
            }
            $full = public_path(ltrim($path, '/'));
            if (file_exists($full)) return $full;
            if (file_exists($url))  return $url;
            try {
                $contents = @file_get_contents($url);
                if ($contents === false) return null;
                $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $tmp = tempnam(sys_get_temp_dir(), 'pemimg_') . '.' . $ext;
                file_put_contents($tmp, $contents);
                return $tmp;
            } catch (\Exception $e) { return null; }
        };

        $formatDate = function ($v) {
            if (!$v) return '-';
            if ($v instanceof \Carbon\Carbon) return $v->format('d/m/Y');
            if (is_string($v) && strtotime($v) !== false) return date('d/m/Y', strtotime($v));
            return $v;
        };

        // ── Style definitions ─────────────────────────────────────────────────
        $titleStyle = [
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0055A5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
        ];
        $sectionHeaderStyle = [
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0055A5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM,
                                             'color'       => ['argb' => 'FF003070']]],
        ];
        $labelStyle = [
            'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E5FAA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'indent'     => 1],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                             'color'       => ['argb' => 'FF7AAAD4']]],
        ];
        $valueStyleEven = [
            'font'      => ['size' => 8],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCE9F7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => false, 'indent' => 1],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                             'color'       => ['argb' => 'FF7AAAD4']]],
        ];
        $valueStyleOdd = [
            'font'      => ['size' => 8],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F6FF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => false, 'indent' => 1],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                             'color'       => ['argb' => 'FF7AAAD4']]],
        ];
        $captionStyle = [
            'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A3A6B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                             'color'       => ['argb' => 'FF3A6EA5']]],
        ];
        $photoStyle = [
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F0F8']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                             'color'       => ['argb' => 'FF3A6EA5']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
        ];
        $emptyPhotoStyle = [
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F4FA']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                           'color'       => ['argb' => 'FFCCDDE8']]],
        ];

        // ── Kumpulkan semua posisi foto ───────────────────────────────────────
        $positions = collect();
        foreach ($this->pemakaian as $p) {
            if ($p->fotoKondisiPemakaian && $p->fotoKondisiPemakaian->isNotEmpty()) {
                foreach ($p->fotoKondisiPemakaian as $f) {
                    $pos = trim(strtolower($f->posisi ?? ''));
                    if ($pos && !$positions->contains($pos)) {
                        $positions->push($pos);
                    }
                }
            }
        }
        $posArray = $positions->values()->toArray();
        $totalFoto = count($posArray);

        // ── Helper: render gambar ke dalam sel ────────────────────────────────
        $placeImage = function ($imgPath, $col, $row, $targetW, $targetH) use ($sheet) {
            $size  = @getimagesize($imgPath);
            $origW = $size[0] ?? $targetW;
            $origH = $size[1] ?? $targetH;

            $scale = min($targetW / max(1, $origW), $targetH / max(1, $origH), 1.0);
            $drawW = (int) round($origW * $scale);
            $drawH = (int) round($origH * $scale);

            $colPx = (int) floor($sheet->getColumnDimension($col)->getWidth() * 7) + 5;
            $rowPx = (int) round(self::ROW_PHOTO_H / 0.75);

            $drawing = new Drawing();
            $drawing->setPath($imgPath);
            $drawing->setCoordinates($col . $row);
            $drawing->setWidth($drawW);
            $drawing->setHeight($drawH);
            $drawing->setOffsetX((int) max(4, floor(($colPx - $drawW) / 2)));
            $drawing->setOffsetY((int) max(4, floor(($rowPx - $drawH) / 2)));
            $drawing->setWorksheet($sheet);
        };

        $row = 4;

        // ── Loop setiap record pemakaian ──────────────────────────────────────
        foreach ($this->pemakaian as $p) {
            $user   = $p->user;
            $mobil  = $p->mobil;
            $detail = $p->detail;

            $displayName = $user->name ?: ($user->username ?? ($user->nip ?? '-'));
            $merekName   = $mobil && $mobil->merek ? $mobil->merek->nama_merek : '-';

            // ── Judul laporan & tanggal ───────────────────────────────────────
            $sheet->setCellValue('A' . $row, 'LAPORAN PEMAKAIAN MOBIL');
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($titleStyle);
            $sheet->getRowDimension($row)->setRowHeight(26);
            $row++;

            $sheet->setCellValue('A' . $row, 'Tanggal Export: ' . date('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font'      => ['italic' => true, 'size' => 8, 'color' => ['argb' => 'FF555555']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(13);
            $row++;

            // ── Header seksi info ─────────────────────────────────────────────
            $sheet->setCellValue('A' . $row, 'INFORMASI PEMAKAIAN');
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
            $sheet->getRowDimension($row)->setRowHeight(16);
            $row++;

            // ── Data info: 2 baris sejajar (label A + nilai B:C) ─────────────
            $pairs = [
                'User (Nama)'       => $displayName,
                'NIP'               => ($user->nip ?? '-'),
                'Role'              => ucfirst($user->role ?? '-'),
                'Mobil (Tipe)'      => ($mobil->tipe ?? '-'),
                'No Polisi'         => ($mobil->no_polisi ?? '-'),
                'Merek'             => $merekName,
                'Penempatan'        => ($mobil && $mobil->penempatan ? ($mobil->penempatan->nama_kantor ?? '-') : '-'),
                'Tujuan'            => ($p->tujuan ?? '-'),
                'Tgl Mulai'         => $formatDate($p->tanggal_mulai ?? null),
                'Tgl Selesai'       => $formatDate($p->tanggal_selesai ?? null),
                'Jarak (km)'        => ($p->jarak_tempuh_km ?? '-'),
                'Jenis Bahan Bakar' => ($p->bahan_bakar ?? '-'),
                'Bar BBM'           => ($p->bahan_bakar_liter ?? '-'),
                'Transmisi'         => ($p->transmisi ?? ($detail ? $detail->transmisi : '-')),
                'KM Awal'           => ($detail ? $detail->kilometer : '-'),
                'Status'            => ucfirst($p->status ?? '-'),
                'Tanggal Buat'      => $formatDate($p->created_at ?? null),
            ];

            $isEven = false;
            foreach ($pairs as $label => $val) {
                $vStyle = $isEven ? $valueStyleEven : $valueStyleOdd;
                $sheet->setCellValue('A' . $row, $label);
                $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
                $sheet->setCellValue('B' . $row, $val);
                $sheet->mergeCells('B' . $row . ':C' . $row);
                $sheet->getStyle('B' . $row . ':C' . $row)->applyFromArray($vStyle);
                $sheet->getRowDimension($row)->setRowHeight(self::ROW_INFO_H);
                $isEven = !$isEven;
                $row++;
            }

            // ── Header seksi foto ─────────────────────────────────────────────
            $sheet->setCellValue('A' . $row, 'DOKUMENTASI FOTO KONDISI');
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($sectionHeaderStyle);
            $sheet->getRowDimension($row)->setRowHeight(16);
            $row++;

            // ── Grid foto ─────────────────────────────────────────────────────
            // Hitung chunk, lalu tangani baris terakhir secara khusus
            $chunks    = array_chunk($posArray, self::PHOTOS_PER_ROW);
            $lastChunk = end($chunks);
            $remainder = count($lastChunk); // 1, 2, atau 3

            foreach ($chunks as $chunkIndex => $chunk) {
                $isLastChunk = ($chunkIndex === count($chunks) - 1);
                $chunkCount  = count($chunk);

                // Tentukan kolom & span untuk baris terakhir yang tidak penuh
                // Jika sisa 1 foto → rentangkan A:C (full width)
                // Jika sisa 2 foto → A (normal) + B:C (melebar)
                // Jika penuh (3)   → A, B, C masing-masing normal
                if ($isLastChunk && $chunkCount < self::PHOTOS_PER_ROW) {
                    if ($chunkCount === 1) {
                        // 1 foto: caption + foto melebar penuh A:C
                        $colSpans = [['col' => 'A', 'merge' => 'A:C', 'pos' => $chunk[0]]];
                    } else {
                        // 2 foto: foto pertama normal di A, foto kedua melebar B:C
                        $colSpans = [
                            ['col' => 'A', 'merge' => null,  'pos' => $chunk[0]],
                            ['col' => 'B', 'merge' => 'B:C', 'pos' => $chunk[1]],
                        ];
                    }
                } else {
                    // Baris penuh: 3 kolom normal
                    $colSpans = [];
                    foreach ($chunk as $i => $pos) {
                        $colSpans[] = ['col' => self::PHOTO_COLS[$i], 'merge' => null, 'pos' => $pos];
                    }
                }

                // ── Baris caption ─────────────────────────────────────────────
                $sheet->getRowDimension($row)->setRowHeight(self::ROW_CAPTION_H);

                // Reset semua kolom caption dulu
                foreach (self::PHOTO_COLS as $col) {
                    $sheet->getStyle($col . $row)->applyFromArray($captionStyle);
                }

                foreach ($colSpans as $cs) {
                    $label = ucwords(str_replace(['_', '-'], ' ', $cs['pos']));
                    $sheet->setCellValue($cs['col'] . $row, $label);
                    if ($cs['merge']) {
                        [$mergeFrom, $mergeTo] = explode(':', $cs['merge']);
                        $sheet->mergeCells($mergeFrom . $row . ':' . $mergeTo . $row);
                        $sheet->getStyle($cs['col'] . $row)->applyFromArray($captionStyle);
                    }
                }
                $row++;

                // ── Baris foto ────────────────────────────────────────────────
                $sheet->getRowDimension($row)->setRowHeight(self::ROW_PHOTO_H);

                // Style semua sel foto dulu
                foreach (self::PHOTO_COLS as $col) {
                    $sheet->getStyle($col . $row)->applyFromArray($photoStyle);
                }

                foreach ($colSpans as $cs) {
                    $col = $cs['col'];

                    // Merge kolom foto jika perlu
                    if ($cs['merge']) {
                        [$mergeFrom, $mergeTo] = explode(':', $cs['merge']);
                        $sheet->mergeCells($mergeFrom . $row . ':' . $mergeTo . $row);
                        $sheet->getStyle($col . $row)->applyFromArray($photoStyle);
                    }

                    // Hitung lebar piksel kolom yang digabung
                    $mergedCols = $cs['merge']
                        ? range(
                            Coordinate::columnIndexFromString(explode(':', $cs['merge'])[0]),
                            Coordinate::columnIndexFromString(explode(':', $cs['merge'])[1])
                          )
                        : [Coordinate::columnIndexFromString($col)];

                    $totalColPx = 0;
                    foreach ($mergedCols as $ci) {
                        $cLetter     = Coordinate::stringFromColumnIndex($ci);
                        $totalColPx += (int) floor($sheet->getColumnDimension($cLetter)->getWidth() * 7) + 5;
                    }

                    // Foto target width sesuai lebar gabungan kolom
                    $targetW = min((int) round($totalColPx * 0.88), self::PHOTO_W * count($mergedCols));
                    $targetH = self::PHOTO_H;

                    // Cari & render foto
                    $found    = null;
                    if ($p->fotoKondisiPemakaian && $p->fotoKondisiPemakaian->isNotEmpty()) {
                        $found = $p->fotoKondisiPemakaian->firstWhere('posisi', $cs['pos']);
                    }
                    $fotoPath = $found ? ($found->foto_sebelum ?? null) : null;
                    $imgPath  = $createTempFromUrl($fotoPath);

                    if ($imgPath) {
                        try {
                            $size  = @getimagesize($imgPath);
                            $origW = $size[0] ?? $targetW;
                            $origH = $size[1] ?? $targetH;

                            $scale = min($targetW / max(1, $origW), $targetH / max(1, $origH), 1.0);
                            $drawW = (int) round($origW * $scale);
                            $drawH = (int) round($origH * $scale);

                            $rowPx = (int) round(self::ROW_PHOTO_H / 0.75);

                            $drawing = new Drawing();
                            $drawing->setPath($imgPath);
                            $drawing->setCoordinates($col . $row);
                            $drawing->setWidth($drawW);
                            $drawing->setHeight($drawH);
                            $drawing->setOffsetX((int) max(4, floor(($totalColPx - $drawW) / 2)));
                            $drawing->setOffsetY((int) max(4, floor(($rowPx - $drawH) / 2)));
                            $drawing->setWorksheet($sheet);
                        } catch (\Exception $e) {
                            $sheet->setCellValue($col . $row, '[error]');
                        }
                    } else {
                        $sheet->setCellValue($col . $row, $fotoPath ? $fotoPath : '-');
                    }
                }
                $row++;
            }

            // ── Pemisah antar record ──────────────────────────────────────────
            $sheet->getRowDimension($row)->setRowHeight(6);
            $row++;
        }

        return $spreadsheet;
    }

    /**
     * Generate & stream sebagai file download XLSX.
     */
    public function download($filename = 'export.xlsx')
    {
        $spreadsheet = $this->generate();
        $writer      = new Xlsx($spreadsheet);

        if (!headers_sent()) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Cache-Control: max-age=0');
        }

        $writer->save('php://output');
        exit;
    }
}
