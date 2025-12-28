@extends('layouts.admin')

@section('title', 'Data Penempatan')

@section('content')
<div class="admin-card">
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="admin-title">Data Penempatan</h3>
            <a href="{{ route('penempatan.create') }}" class="admin-btn primary"><i class="fas fa-plus"></i> Tambah Penempatan</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('penempatan.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control" placeholder="Cari nama kantor, kode, alamat...">
            </div>
            <div class="col-md-4">
                <button class="admin-btn primary w-100">Cari</button>
            </div>
        </form>
    </div>

    <div class="mb-3 d-flex gap-2">
        <button id="exportPenempatanBtn" class="admin-btn primary"><i class="fas fa-download"></i> Export Terpilih</button>
    </div>

    <div class="table-admin">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px"><input type="checkbox" id="selectAllPenempatan"></th>
                        <th style="width:50px">No</th>
                        <th>Kode Kantor</th>
                        <th>Nama Kantor</th>
                        <th>Alamat</th>
                        <th>Kota</th>
                        <th>Provinsi</th>
                        <th style="width:180px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penempatans as $p)
                    <tr>
                        <td><input type="checkbox" class="penempatan-checkbox" value="{{ $p->id }}"></td>
                        <td><strong>{{ $loop->iteration + ($penempatans->currentPage()-1) * $penempatans->perPage() }}</strong></td>
                        <td><span class="badge bg-primary">{{ $p->kode_kantor }}</span></td>
                        <td><strong>{{ $p->nama_kantor }}</strong></td>
                        <td>{{ $p->alamat }}</td>
                        <td>{{ $p->kota }}</td>
                        <td>{{ $p->provinsi }}</td>
                        <td>
                            <a href="{{ route('penempatan.edit', $p->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <form action="{{ route('penempatan.destroy', $p->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus penempatan ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="admin-btn danger btn-sm"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center muted">Data tidak ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $penempatans->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

@endsection

<script>
document.getElementById('selectAllPenempatan').addEventListener('change', function() {
    document.querySelectorAll('.penempatan-checkbox').forEach(cb => cb.checked = this.checked);
});
document.getElementById('exportPenempatanBtn').addEventListener('click', function() {
    const ids = Array.from(document.querySelectorAll('.penempatan-checkbox:checked')).map(cb => cb.value);
    if (!ids.length) return alert('Pilih minimal satu penempatan untuk diexport.');
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.tools.export') }}';
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">
                      <input type="hidden" name="model" value="penempatan">`;
    ids.forEach(id => form.innerHTML += `<input type="hidden" name="ids[]" value="${id}">`);
    document.body.appendChild(form);
    form.submit();
});
</script>
