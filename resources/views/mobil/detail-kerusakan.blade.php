@extends('layouts.admin')

@section('title', 'Detail Kerusakan Mobil')

@section('content')
<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="fas fa-car-crash text-danger"></i> Detail Kerusakan - {{ e($mobil->no_polisi) }}</h3>
        <div class="d-flex gap-2">
            @if(Auth::user() && in_array(optional(Auth::user())->role ?? '', ['admin']))
                @php
                    $isRusak = false;
                    if (optional($mobil->detail)->kondisi && stripos(optional($mobil->detail)->kondisi, 'rusak') !== false) {
                        $isRusak = true;
                    }
                    if (!$isRusak && isset($mobil->laporanRusak) && count($mobil->laporanRusak)) {
                        $isRusak = true;
                    }
                @endphp

                @if($isRusak)
                    <form action="{{ route('mobil.setAvailable', $mobil->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="admin-btn success" onclick="return confirm('Tandai mobil sebagai tersedia dan hapus laporan kerusakan?')">Set Available</button>
                    </form>
                @endif
            @endif

            <a href="{{ route('mobil.index') }}" class="admin-btn secondary">Kembali</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>No Polisi:</strong> {{ e($mobil->no_polisi) }}</p>
            <p><strong>Merek:</strong> {{ e(optional($mobil->merek)->nama_merek) }}</p>
            <p><strong>Jenis:</strong> {{ e(optional($mobil->jenis)->nama_jenis) }}</p>
            <p><strong>Penempatan:</strong> {{ e(optional($mobil->penempatan)->nama_kantor) }}</p>
            <p><strong>Kondisi detail:</strong> {{ e(optional($mobil->detail)->kondisi ?? '-') }}</p>
        </div>
    </div>

    <h5>Laporan Kerusakan</h5>
    @if(isset($mobil->laporanRusak) && count($mobil->laporanRusak))
        @foreach($mobil->laporanRusak as $laporan)
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Laporan #{{ $laporan->id }}</strong>
                    <span class="text-muted"> - {{ $laporan->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="card-body">
                    <p><strong>Pelapor:</strong> {{ e(optional($laporan->user)->username ?? optional($laporan->user)->nip ?? 'N/A') }}</p>
                    <p><strong>Kondisi yang dilaporkan:</strong> {{ e($laporan->kondisi) }}</p>
                    <p><strong>Catatan:</strong> {{ e($laporan->catatan ?? '-') }}</p>
                    <p><strong>Lokasi:</strong> {{ e($laporan->lokasi ?? '-') }}</p>

                    <div class="row">
                        @if(isset($laporan->fotos) && count($laporan->fotos))
                            @foreach($laporan->fotos as $foto)
                                <div class="col-md-3 mb-2">
                                    <a href="{{ $foto->file_path }}" target="_blank">
                                        <img src="{{ $foto->file_path }}" class="img-fluid rounded" style="max-height:160px;object-fit:cover;">
                                    </a>
                                    <div class="small text-muted">Posisi: {{ e($foto->posisi) }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-muted">Tidak ada foto</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-info">Tidak ditemukan laporan kerusakan untuk mobil ini.</div>
    @endif
</div>
@endsection
