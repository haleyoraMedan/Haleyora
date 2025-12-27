@extends('layouts.admin')

@section('title', 'Data Mobil')

@section('content')
<div class="admin-card">

    <!-- Toolbar -->
    <div class="admin-toolbar mb-3">
        <h2 class="admin-title">Data Mobil</h2>
        <div class="ms-auto">
            <a href="{{ route('mobil.create') }}" class="admin-btn primary">+ Tambah Mobil</a>
        </div>
    </div>

    <!-- Form Pencarian -->
    <form method="GET" action="{{ route('mobil.index') }}" class="mb-3 d-flex gap-2 align-items-end">
        <div class="flex-grow-1">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="No Polisi, Merek, Tipe..." value="{{ request('search') }}">
        </div>
        <button type="submit" class="admin-btn primary">Cari</button>
    </form>

    <!-- Tabel Index -->
    @if(isset($mobils))
    <div class="table-admin">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>No Polisi</th>
                    <th>Merek</th>
                    <th>Jenis</th>
                    <th>Tipe</th>
                    <th>Tahun</th>
                    <th>Warna</th>
                    <th>Penempatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mobils as $mobil)
                <tr>
                    <td>{{ $loop->iteration + ($mobils->currentPage()-1) * $mobils->perPage() }}</td>
                    <td>{{ e($mobil->no_polisi) }}</td>
                    <td>{{ e($mobil->merek->nama_merek ?? '-') }}</td>
                    <td>{{ e($mobil->jenis->nama_jenis ?? '-') }}</td>
                    <td>{{ e($mobil->tipe) }}</td>
                    <td>{{ e($mobil->tahun) }}</td>
                    <td>{{ e($mobil->warna) }}</td>
                    <td>
                        @if($mobil->penempatan)
                            <div class="mb-2">
                                <strong>{{ e($mobil->penempatan->nama_kantor) }}</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#penempatanModal"
                                data-pem-id="{{ $mobil->penempatan->id }}"
                                data-pem-kode="{{ e($mobil->penempatan->kode_kantor) }}"
                                data-pem-alamat="{{ e($mobil->penempatan->alamat) }}"
                                data-pem-kota="{{ e($mobil->penempatan->kota) }}"
                                data-pem-provinsi="{{ e($mobil->penempatan->provinsi) }}">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('mobil.edit', $mobil->id) }}" class="btn btn-sm btn-info">Edit</a>
                        @if(!$mobil->is_deleted)
                        <form action="{{ route('mobil.destroy', $mobil->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus mobil ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">Data tidak ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $mobils->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- Modal Detail Penempatan -->
<div class="modal fade" id="penempatanModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="fas fa-map-marker-alt"></i> Detail Penempatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small text-muted">ID</label>
                    <p class="fw-bold mb-0" id="modal-id">-</p>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Kode Kantor</label>
                    <p class="fw-bold mb-0" id="modal-kode">-</p>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Alamat</label>
                    <p class="mb-0" id="modal-alamat">-</p>
                </div>
                <div class="row">
                    <div class="col-6">
                        <label class="form-label small text-muted">Kota</label>
                        <p class="fw-bold mb-0" id="modal-kota">-</p>
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">Provinsi</label>
                        <p class="fw-bold mb-0" id="modal-provinsi">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const penempatanModal = document.getElementById('penempatanModal');
    if (!penempatanModal) return;
    
    penempatanModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        if (!button) return;
        
        const id = button.getAttribute('data-pem-id') ?? '-';
        const kode = button.getAttribute('data-pem-kode') ?? '-';
        const alamat = button.getAttribute('data-pem-alamat') ?? '-';
        const kota = button.getAttribute('data-pem-kota') ?? '-';
        const provinsi = button.getAttribute('data-pem-provinsi') ?? '-';

        document.getElementById('modal-id').textContent = id;
        document.getElementById('modal-kode').textContent = kode;
        document.getElementById('modal-alamat').textContent = alamat;
        document.getElementById('modal-kota').textContent = kota;
        document.getElementById('modal-provinsi').textContent = provinsi;
        
        console.log('Modal data loaded:', {id, kode, alamat, kota, provinsi});
    });
});
</script>
@endsection
