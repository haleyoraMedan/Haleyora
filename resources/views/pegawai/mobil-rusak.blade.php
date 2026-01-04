@extends('layouts.pegawai')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0">
                <i class="fas fa-exclamation-triangle"></i> Daftar Mobil Rusak
            </h4>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($mobilRusak->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Tidak ada mobil dengan kondisi rusak saat ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>No Polisi</th>
                                <th>Merek</th>
                                <th>Status Rusak</th>
                                <th>Penempatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mobilRusak as $mobil)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-bold font-monospace">{{ $mobil->no_polisi }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $mobil->merek->nama_merek ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ $mobil->detail->kondisi ?? 'Rusak' }}
                                        </span>
                                    </td>
                                    <td>{{ $mobil->penempatan->nama_kantor ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('pegawai.lapor-rusak', $mobil->id) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Update Rusak
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox"></i> Tidak ada data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
