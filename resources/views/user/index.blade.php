@extends('layouts.admin')

@section('title', 'Data User')

@section('content')
<div class="admin-card">
    <div class="mb-3">
        <form action="{{ route('user.index') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control" placeholder="Cari username, nip, role...">
            </div>
            <div class="col-md-4">
                <button class="admin-btn primary w-100">Cari</button>
            </div>
        </form>
    </div>

    <div class="table-admin">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:50px">No</th>
                    <th>NIP</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Penempatan</th>
                    <th style="width:220px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $u->nip }}</td>
                    <td>{{ $u->username }}</td>
                    <td>
                        <span class="badge {{ $u->role === 'admin' ? 'bg-danger' : 'bg-info' }}">{{ ucfirst($u->role) }}</span>
                    </td>
                    <td>
                        @if($u->penempatan)
                            {{ $u->penempatan->nama_kantor }}
                            <button class="btn btn-sm btn-secondary mt-1" data-bs-toggle="modal" data-bs-target="#penempatanModal" onclick="openModal({{ $u->penempatan->id }}, '{{ $u->penempatan->kode_kantor }}', '{{ $u->penempatan->alamat }}', '{{ $u->penempatan->kota }}', '{{ $u->penempatan->provinsi }}')">Detail</button>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('user.edit', $u->id) }}" class="btn btn-sm btn-warning">Edit</a>

                        @if(!$u->is_deleted)
                            <form action="{{ route('user.destroy', $u->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus user ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="admin-btn danger btn-sm">Hapus</button>
                            </form>
                        @endif

                        @if($u->is_deleted)
                            <form action="{{ route('user.restore', $u->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="admin-btn success btn-sm">Restore</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center muted">Data tidak ditemukan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<!-- Modal -->
<div class="modal fade" id="penempatanModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Penempatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="modal-id"></span></p>
                <p><strong>Kode Kantor:</strong> <span id="modal-kode"></span></p>
                <p><strong>Alamat:</strong> <span id="modal-alamat"></span></p>
                <p><strong>Kota:</strong> <span id="modal-kota"></span></p>
                <p><strong>Provinsi:</strong> <span id="modal-provinsi"></span></p>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(id, kode, alamat, kota, provinsi) {
    document.getElementById('modal-id').innerText = id;
    document.getElementById('modal-kode').innerText = kode;
    document.getElementById('modal-alamat').innerText = alamat;
    document.getElementById('modal-kota').innerText = kota;
    document.getElementById('modal-provinsi').innerText = provinsi;
}
</script>

@endsection