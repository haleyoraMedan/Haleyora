@extends('layouts.admin')

@section('title', 'Data Mobil')

@section('content')
<div class="admin-card">

    <!-- Toolbar -->
    <div class="admin-toolbar mb-3">
        <h2 class="admin-title"><i class="fas fa-car"></i> Data Mobil</h2>
        <div class="ms-auto d-flex gap-2">
            <button id="exportMobilBtn" class="admin-btn primary">
                <i class="fas fa-download"></i> Export Terpilih
            </button>
            <button id="bulkDeleteMobilBtn" class="admin-btn danger">
                <i class="fas fa-trash"></i> Hapus Terpilih
            </button>
            @if(request('show_deleted') == '1')
                <a href="{{ route('mobil.index') }}" class="admin-btn">Tampilkan Semua</a>
            @else
                <a href="{{ route('mobil.index', array_merge(request()->query(), ['show_deleted'=>1])) }}" class="admin-btn">Tampilkan Terhapus</a>
            @endif
            <a href="{{ route('mobil.create') }}" class="admin-btn primary">
                <i class="fas fa-plus"></i> Tambah Mobil
            </a>
        </div>
    </div>

    <!-- Form Pencarian -->
    <form method="GET" action="{{ route('mobil.index') }}" class="mb-3 d-flex gap-2 align-items-end flex-wrap">
             <div class="flex-grow-1" style="min-width: 250px;">
            <label class="form-label"><i class="fas fa-search"></i> Cari</label>
            <input type="text" name="search" class="form-control"
                 placeholder="No Polisi, Merek..."
                   value="{{ request('search') ?? '' }}">
        </div>
        <div style="min-width:200px;">
            <label class="form-label">Urut Berdasarkan</label>
            <div class="d-flex gap-2">
                <select name="sort" class="form-select">
                    <option value="">Default</option>
                    <option value="kondisi" {{ request('sort')=='kondisi' ? 'selected' : '' }}>Kondisi</option>
                </select>
                <select name="dir" class="form-select" style="width:110px;">
                    <option value="asc" {{ request('dir','asc')=='asc' ? 'selected' : '' }}>Naik</option>
                    <option value="desc" {{ request('dir')=='desc' ? 'selected' : '' }}>Turun</option>
                </select>
            </div>
        </div>
        <button type="submit" class="admin-btn primary">
            <i class="fas fa-search"></i> Cari
        </button>
        <a href="{{ route('mobil.index') }}" class="admin-btn">
            <i class="fas fa-redo"></i> Reset
        </a>
    </form>

    <!-- Table -->
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
                        <th width="10%">Tahun</th>
                        <th width="10%">Kondisi</th>
                        <th width="10%">Warna</th>
                        <th width="15%">Penempatan</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($mobils as $mobil)
                    <tr class="{{ $mobil->is_deleted ? 'table-danger opacity-75' : '' }}">
                        <td>
                            <input type="checkbox"
                                   class="mobil-checkbox"
                                   value="{{ $mobil->id }}"
                                   {{ $mobil->is_deleted ? 'disabled' : '' }}>
                        </td>
                        <td>
                            {{ $loop->iteration + ($mobils->currentPage()-1) * $mobils->perPage() }}
                        </td>
                        <td class="fw-bold font-monospace">
                            {{ e($mobil->no_polisi) }}
                            @if($mobil->is_deleted)
                                <span class="badge bg-danger ms-1">DELETED</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">
                                {{ e($mobil->merek->nama_merek ?? '-') }}
                            </span>
                        </td>
                        <td>{{ e($mobil->jenis->nama_jenis ?? '-') }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ e($mobil->tahun) }}</span>
                        </td>
                        <td>
                            {{ e(optional($mobil->detail)->kondisi ?? '-') }}
                        </td>
                        <td>{{ e($mobil->warna) }}</td>
                        <td>
                            @if($mobil->penempatan)
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#penempatanModal-{{ $mobil->id }}">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ e($mobil->penempatan->nama_kantor) }}
                                </button>
                            @else
                                <span class="text-muted">Tidak ada</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">

                                {{-- EDIT --}}
                            
                                    <a href="{{ route('mobil.edit', $mobil->id) }}"
                                       class="btn btn-sm btn-info"
                                       title="Edit mobil">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    {{-- LAPOR RUSAK removed for admin; pegawai reports via pegawai views --}}

                                    {{-- SET AVAILABLE (ADMIN) --}}
                                    @if(Auth::user() && in_array(optional(Auth::user())->role ?? '', ['admin']))
                                        @if(optional($mobil->detail)->kondisi && strpos(strtolower(optional($mobil->detail)->kondisi), 'rusak') !== false)
                                            <form action="{{ route('mobil.setAvailable', $mobil->id) }}" method="POST" style="display:inline;margin-left:6px;">
                                                @csrf
                                                <button class="btn btn-sm btn-success" onclick="return confirm('Tandai mobil sebagai tersedia?')"><i class="fas fa-check"></i> Set Available</button>
                                            </form>
                                        @endif
                                    @endif

                                    @if($mobil->is_deleted)
                                        <form action="{{ route('mobil.restore', $mobil->id) }}" method="POST" style="display:inline;margin-left:6px;">
                                            @csrf
                                            <button class="admin-btn success btn-sm">Restore</button>
                                        </form>
                                    @endif

                                

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
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

{{-- MODAL PENEMPATAN --}}
@foreach($mobils as $mobil)
@if($mobil->penempatan)
<div class="modal fade" id="penempatanModal-{{ $mobil->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fas fa-map-marker-alt text-danger"></i> Detail Penempatan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Kode:</strong> {{ e($mobil->penempatan->kode_kantor) }}</p>
                <p><strong>Nama:</strong> {{ e($mobil->penempatan->nama_kantor) }}</p>
                <p><strong>Alamat:</strong> {{ e($mobil->penempatan->alamat) }}</p>
                <p><strong>Kota/Kabupaten:</strong> {{ e($mobil->penempatan->kota) }}</p>
                <p><strong>Provinsi:</strong> {{ e($mobil->penempatan->provinsi) }}</p>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

{{-- MODAL LAPOR RUSAK removed for admin --}}

<script>
// Debug: Log semua modal yang ada
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded');
});

// Select all checkbox
document.getElementById('selectAllMobil')?.addEventListener('change', e => {
    document.querySelectorAll('.mobil-checkbox:not(:disabled)')
        .forEach(cb => cb.checked = e.target.checked);
});

// Export button
document.getElementById('exportMobilBtn')?.addEventListener('click', () => {
    const ids = [...document.querySelectorAll('.mobil-checkbox:checked')]
        .map(cb => cb.value);

    if (!ids.length) return alert('Pilih minimal satu mobil');

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.tools.export') }}';

    form.innerHTML = `
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="model" value="mobil">
        ${ids.map(id => `<input type="hidden" name="ids[]" value="${id}">`).join('')}
    `;

    document.body.appendChild(form);
    form.submit();
});

// Bulk delete selected mobil
document.getElementById('bulkDeleteMobilBtn')?.addEventListener('click', () => {
    const ids = [...document.querySelectorAll('.mobil-checkbox:checked')].map(cb => cb.value);
    if (!ids.length) return alert('Pilih minimal satu mobil');
    if (!confirm('Yakin menghapus mobil terpilih?')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('mobil.bulkDestroy') }}';
    form.innerHTML = `
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        ${ids.map(id => `<input type="hidden" name="ids[]" value="${id}">`).join('')}
    `;
    document.body.appendChild(form);
    form.submit();
});
</script> 
@endsection
