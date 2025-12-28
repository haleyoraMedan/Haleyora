<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PemakaianMobilExport
{
    protected $pemakaian;

    public function __construct($pemakaian)
    {
        $this->pemakaian = $pemakaian;
    }

    public function generate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pemakaian Mobil');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(12);
        $sheet->getColumnDimension('M')->setWidth(15);
        $sheet->getColumnDimension('N')->setWidth(15);
        $sheet->getColumnDimension('O')->setWidth(12);
        $sheet->getColumnDimension('P')->setWidth(15);
        $sheet->getColumnDimension('Q')->setWidth(15);
        $sheet->getColumnDimension('R')->setWidth(15);
        $sheet->getColumnDimension('S')->setWidth(15);
        $sheet->getColumnDimension('T')->setWidth(25);
        $sheet->getColumnDimension('U')->setWidth(25);

        // Title and date will be set after header is built (dynamic columns)

        

        // Header row
        $row = 4;

        // Determine dynamic foto positions across all pemakaian (only foto_sebelum)
        $positions = collect();
        foreach ($this->pemakaian as $p) {
            if ($p->fotoKondisiPemakaian && $p->fotoKondisiPemakaian->isNotEmpty()) {
                foreach ($p->fotoKondisiPemakaian as $f) {
                    $pos = trim(strtolower($f->posisi ?? ''));
                    if (!$pos) continue;
                    if (!$positions->contains($pos)) $positions->push($pos);
                }
            }
        }

        // Base headers (removed Email and Kondisi Awal; fixed Bahan Bakar label)
        $headers = [
            'No', 'User (Nama)', 'NIP', 'Role',
            'Mobil (Tipe)', 'No Polisi', 'Merek', 'Penempatan',
            'Tujuan', 'Tgl Mulai', 'Tgl Selesai', 'Jarak (km)',
            'Jenis Bahan Bakar', 'Liter BBM', 'Transmisi',
            'KM Awal', 'Status', 'Tanggal Buat'
        ];

        // Add a Foto column per position (only foto_sebelum)
        foreach ($positions as $pos) {
            $headers[] = 'Foto - ' . ucwords(str_replace(['_', '-'], ' ', $pos));
        }

        // Write header
        for ($col = 1; $col <= count($headers); $col++) {
            $cellCoord = Coordinate::stringFromColumnIndex($col) . $row;
            $cell = $sheet->getCell($cellCoord);
            $cell->setValue($headers[$col - 1]);
            $cell->getStyle()
                ->getFont()->setBold(true)->setColor(new Color('FFFFFFFF'));
            $cell->getStyle()
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0066CC');
            $cell->getStyle()
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        }

        $sheet->getRowDimension($row)->setRowHeight(25);

        // Set title and date rows dynamically based on header width
        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $titleCell = $sheet->getCell('A1');
        $titleCell->setValue('LAPORAN PEMAKAIAN MOBIL');
        $titleCell->getStyle()->getFont()->setBold(true)->setSize(14);
        $titleCell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $sheet->mergeCells('A2:' . $lastCol . '2');
        $dateCell = $sheet->getCell('A2');
        $dateCell->setValue('Tanggal Export: ' . date('d/m/Y H:i:s'));
        $dateCell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // Data rows
        $row = 5;
        $no = 1;
        foreach ($this->pemakaian as $p) {
            $user = $p->user;
            $mobil = $p->mobil;
            $detail = $p->detail;

            $displayName = $user->name ?: ($user->username ?? ($user->nip ?? '-'));
            $merekName = $mobil && $mobil->merek ? $mobil->merek->nama_merek : '-';

            // prepare base values in the same order as $headers base part
            $values = [
                $no,
                $displayName,
                $user->nip ?? '-',
                ucfirst($user->role ?? '-'),
                $mobil->tipe ?? '-',
                $mobil->no_polisi ?? '-',
                $merekName,
                $mobil && $mobil->penempatan ? ($mobil->penempatan->nama_kantor ?? '-') : '-',
                $p->tujuan ?? '-',
                $p->tanggal_mulai ?? '-',
                $p->tanggal_selesai ?? '-',
                $p->jarak_tempuh_km ?? '-',
                $p->bahan_bakar ?? '-',
                $p->bahan_bakar_liter ?? '-',
                $p->transmisi ?? ($detail ? $detail->transmisi : '-'),
                $detail ? $detail->kilometer : '-',
                ucfirst($p->status ?? '-'),
                $p->created_at ? $p->created_at->format('d/m/Y H:i') : '-',
            ];

            // write base values
            for ($i = 0; $i < count($values); $i++) {
                $colLetter = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue($colLetter . $row, $values[$i]);
            }

            // helper to resolve path and create a temp file if needed
            $createTempFromUrl = function($url) {
                if (!$url) return null;
                try {
                    $base = asset('');
                } catch (\Throwable $e) {
                    $base = null;
                }

                $path = $url;
                if ($base && strpos($url, $base) === 0) {
                    $path = str_replace($base, '', $url);
                }

                $full = public_path(ltrim($path, '/'));
                if (file_exists($full)) return $full;
                if (file_exists($url)) return $url;

                try {
                    $contents = @file_get_contents($url);
                    if ($contents === false) return null;
                    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $tmp = tempnam(sys_get_temp_dir(), 'pemimg_') . '.' . $ext;
                    file_put_contents($tmp, $contents);
                    return $tmp;
                } catch (\Exception $e) {
                    return null;
                }
            };

            // embed foto_sebelum for each known position
            $baseCount = count($values);
            $posIndex = 0;
            foreach ($positions as $pos) {
                $posIndex++;
                $colIdx = $baseCount + $posIndex; // 1-based
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);

                $found = null;
                if ($p->fotoKondisiPemakaian && $p->fotoKondisiPemakaian->isNotEmpty()) {
                    $found = $p->fotoKondisiPemakaian->firstWhere('posisi', $pos);
                }

                $fotoPath = $found ? ($found->foto_sebelum ?? null) : null;
                $imgPath = $createTempFromUrl($fotoPath);

                if ($imgPath) {
                    try {
                        $drawing = new Drawing();
                        $drawing->setPath($imgPath);
                        $drawing->setCoordinates($colLetter . $row);
                        $drawing->setHeight(80);
                        $drawing->setOffsetX(5);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($sheet);
                    } catch (\Exception $e) {
                        $sheet->setCellValue($colLetter . $row, $fotoPath);
                    }
                } else {
                    $sheet->setCellValue($colLetter . $row, $fotoPath ? $fotoPath : '-');
                }
            }

            // Apply borders and alignment for all columns
            for ($col = 1; $col <= count($headers); $col++) {
                $cellCoord = Coordinate::stringFromColumnIndex($col) . $row;
                $cell = $sheet->getCell($cellCoord);
                $cell->getStyle()->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
                $cell->getStyle()->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }

            // increase row height to fit images
            $sheet->getRowDimension($row)->setRowHeight(85);

            $row++;
            $no++;
        }

        // Freeze header rows
        $sheet->freezePane('A5');

        // Add summary section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'RINGKASAN');
        $sheet->getCell('A' . $row)->getStyle()->getFont()->setBold(true)->setSize(12);

        $row++;
        $sheet->setCellValue('A' . $row, 'Total Pemakaian: ');
        $sheet->setCellValue('B' . $row, count($this->pemakaian));

        $row++;
        $approvedCount = $this->pemakaian->where('status', 'approved')->count();
        $sheet->setCellValue('A' . $row, 'Status Approved: ');
        $sheet->setCellValue('B' . $row, $approvedCount);

        $row++;
        $pendingCount = $this->pemakaian->where('status', 'pending')->count();
        $sheet->setCellValue('A' . $row, 'Status Pending: ');
        $sheet->setCellValue('B' . $row, $pendingCount);

        $row++;
        $rejectedCount = $this->pemakaian->where('status', 'rejected')->count();
        $sheet->setCellValue('A' . $row, 'Status Rejected: ');
        $sheet->setCellValue('B' . $row, $rejectedCount);

        $row++;
        $availableCount = $this->pemakaian->where('status', 'available')->count();
        $sheet->setCellValue('A' . $row, 'Status Available: ');
        $sheet->setCellValue('B' . $row, $availableCount);

        return $spreadsheet;
    }

    /**
     * Generate and send the spreadsheet as a download (XLSX).
     */
    public function download($filename = 'export.xlsx')
    {
        $spreadsheet = $this->generate();
        $writer = new Xlsx($spreadsheet);

        // Send headers and output the file
        if (!headers_sent()) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Cache-Control: max-age=0');
        }

        $writer->save('php://output');
        // ensure script terminates after output
        exit;
    }

}
