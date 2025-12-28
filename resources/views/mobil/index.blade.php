@extends('layouts.admin')

@section('title', 'Data Mobil')

@section('content')
<div class="admin-card">

    <!-- Toolbar -->
    <div class="admin-toolbar mb-3">
        <h2 class="admin-title"><i class="fas fa-car"></i> Data Mobil</h2>
        <div class="ms-auto d-flex gap-2">
            <button id="exportMobilBtn" class="admin-btn primary"><i class="fas fa-download"></i> Export Terpilih</button>
            <a href="{{ route('mobil.create') }}" class="admin-btn primary"><i class="fas fa-plus"></i> Tambah Mobil</a>
        </div>
    </div>

    <!-- Form Pencarian -->
    <form method="GET" action="{{ route('mobil.index') }}" class="mb-3 d-flex gap-2 align-items-end flex-wrap">
        <div class="flex-grow-1" style="min-width: 250px;">
            <label class="form-label"><i class="fas fa-search"></i> Cari</label>
            <input type="text" name="search" class="form-control" placeholder="No Polisi, Merek, Tipe..." value="{{ request('search') ?? '' }}">
        </div>
        <button type="submit" class="admin-btn primary"><i class="fas fa-search"></i> Cari</button>
        <a href="{{ route('mobil.index') }}" class="admin-btn"><i class="fas fa-redo"></i> Reset</a>
    </form>

    <!-- Tabel Index -->
    <div class="table-admin">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="5%"><input type="checkbox" id="selectAllMobil"></th>
                        <th width="5%">No</th>
                        <th width="12%">No Polisi</th>
                        <th width="12%">Merek</th>
                        <th width="10%">Jenis</th>
                        <th width="10%">Tipe</th>
                        <th width="8%">Tahun</th>
                        <th width="10%">Warna</th>
                        <th width="18%">Penempatan</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mobils as $mobil)
                    <tr class="align-middle">
                        <td><input type="checkbox" class="mobil-checkbox" value="{{ $mobil->id }}"></td>
                        <td><strong>{{ $loop->iteration + ($mobils->currentPage()-1) * $mobils->perPage() }}</strong></td>
                        <td>
                            <div class="font-monospace fw-bold">{{ e($mobil->no_polisi) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ e($mobil->merek->nama_merek ?? '-') }}</span>
                        </td>
                        <td>{{ e($mobil->jenis->nama_jenis ?? '-') }}</td>
                        <td>{{ e($mobil->tipe) }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ e($mobil->tahun) }}</span>
                        </td>
                        <td>
                            <small class="text-muted">{{ e($mobil->warna) }}</small>
                        </td>
                        <td>
                            @if($mobil->penempatan)
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#penempatanModal-{{ $mobil->penempatan->id }}">
                                    <i class="fas fa-map-marker-alt"></i> {{ e($mobil->penempatan->nama_kantor) }}
                                </button>
                            @else
                                <span class="text-muted"><i class="fas fa-minus-circle"></i> Tidak ada penempatan</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('mobil.edit', $mobil->id) }}" class="btn btn-sm btn-info" title="Edit mobil">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                @if(!$mobil->is_deleted)
                                <form action="{{ route('mobil.destroy', $mobil->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus mobil ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus mobil">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-inbox"></i> Data mobil tidak ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $mobils->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

</div>

<!-- Server-rendered modals per penempatan -->
@foreach($mobils as $mobil)
    @if($mobil->penempatan)
        <div class="modal fade" id="penempatanModal-{{ $mobil->penempatan->id }}" tabindex="-1" aria-labelledby="penempatanModalLabel-{{ $mobil->penempatan->id }}" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title" id="penempatanModalLabel-{{ $mobil->penempatan->id }}">
                            <i class="fas fa-map-marker-alt text-danger"></i> Detail Penempatan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted"><i class="fas fa-id-badge"></i> ID Penempatan</label>
                            <p class="fw-bold mb-0 ps-3">{{ $mobil->penempatan->id }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted"><i class="fas fa-barcode"></i> Kode Kantor</label>
                            <p class="fw-bold mb-0 ps-3">{{ e($mobil->penempatan->kode_kantor) }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted"><i class="fas fa-home"></i> Nama Kantor</label>
                            <p class="fw-bold mb-0 ps-3">{{ e($mobil->penempatan->nama_kantor) }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted"><i class="fas fa-address-card"></i> Alamat</label>
                            <p class="mb-0 ps-3">{{ e($mobil->penempatan->alamat) }}</p>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label small text-muted"><i class="fas fa-city"></i> Kota</label>
                                <p class="fw-bold mb-0 ps-3">{{ e($mobil->penempatan->kota) }}</p>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted"><i class="fas fa-map"></i> Provinsi</label>
                                <p class="fw-bold mb-0 ps-3">{{ e($mobil->penempatan->provinsi) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach


<script>
// Consolidated safe handlers for select-all and export
document.addEventListener('change', function(e) {
    if (!e.target) return;
    if (e.target.id === 'selectAllMobil') {
        document.querySelectorAll('.mobil-checkbox').forEach(cb => cb.checked = e.target.checked);
    }
});

document.addEventListener('click', function(e) {
    if (!e.target) return;
    if (e.target.id === 'exportMobilBtn') {
        const ids = Array.from(document.querySelectorAll('.mobil-checkbox:checked')).map(cb => cb.value);
        if (!ids.length) return alert('Pilih minimal satu mobil untuk diexport.');

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.tools.export') }}';
        form.style.display = 'none';

        const token = document.createElement('input'); token.type = 'hidden'; token.name = '_token'; token.value = '{{ csrf_token() }}'; form.appendChild(token);
        const model = document.createElement('input'); model.type = 'hidden'; model.name = 'model'; model.value = 'mobil'; form.appendChild(model);
        ids.forEach(id => { const inp = document.createElement('input'); inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id; form.appendChild(inp); });

        document.body.appendChild(form);
        form.submit();
    }
});
</script>

@endsection