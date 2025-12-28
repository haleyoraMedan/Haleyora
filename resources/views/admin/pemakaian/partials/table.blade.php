<div class="table-responsive">
    <table class="table table-hover table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th width="5%"><input type="checkbox" id="selectAllRows"></th>
                <th width="5%">No</th>
                <th width="15%">Mobil</th>
                <th width="20%">Pengguna</th>
                <th width="20%">Tujuan</th>
                <th width="12%">Tanggal</th>
                <th width="10%">Status</th>
                <th width="18%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pemakaian as $index => $p)
            <tr data-id="{{ $p->id }}" class="align-middle">
            <td><input type="checkbox" class="row-checkbox" value="{{ $p->id }}"></td>
                <td><strong>{{ ($pemakaian->currentPage() - 1) * $pemakaian->perPage() + $loop->iteration }}</strong></td>
                <td>
                    <div class="font-monospace">{{ $p->mobil->no_polisi }}</div>
                    <small class="text-muted">{{ $p->mobil->merek->nama_merek ?? '-' }}</small>
                </td>
                <td>
                    <div><strong>{{ $p->user->name }}</strong></div>
                    <small class="text-muted">{{ $p->user->nip ?? '-' }}</small>
                    <br>
                    <small class="text-secondary">{{ $p->user->email }}</small>
                </td>
                <td>{{ $p->tujuan }}</td>
                <td>
                    <div>{{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/Y') }}</div>
                    <small class="text-muted">s/d {{ $p->tanggal_selesai ? \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') : '-' }}</small>
                </td>
                <td>
                    @if($p->status === 'pending')
                        <span class="badge bg-warning text-dark">⏳ Pending</span>
                    @elseif($p->status === 'approved')
                        <span class="badge bg-success">✓ Approved</span>
                    @elseif($p->status === 'available')
                        <span class="badge bg-info">◆ Available</span>
                    @else
                        <span class="badge bg-danger">✗ Rejected</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <button class="btn btn-sm btn-info btn-detail" data-id="{{ $p->id }}" title="Lihat detail">
                            <i class="fas fa-eye"></i> Detail
                        </button>
                        <button class="btn btn-sm btn-outline-primary btn-toggle-status" data-id="{{ $p->id }}" title="Ubah status">
                            <i class="fas fa-edit"></i> Ubah Status
                        </button>
                        @if(auth()->user() && (auth()->user()->role === 'admin' || (auth()->id() === $p->user_id && $p->status === 'pending')))
                            <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $p->id }}" title="Hapus">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        @endif
                    </div>
                    <div class="mt-2 status-controls d-none" data-id="{{ $p->id }}">
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <select class="form-select form-select-sm status-select" data-id="{{ $p->id }}" style="flex: 1; min-width: 140px;">
                                <option value="pending" {{ $p->status=='pending' ? 'selected' : '' }}>⏳ Pending</option>
                                <option value="approved" {{ $p->status=='approved' ? 'selected' : '' }}>✓ Approved</option>
                                <option value="rejected" {{ $p->status=='rejected' ? 'selected' : '' }}>✗ Rejected</option>
                                <option value="available" {{ $p->status=='available' ? 'selected' : '' }}>◆ Available</option>
                            </select>
                            <button class="btn btn-sm btn-success btn-update-status" data-id="{{ $p->id }}" title="Simpan">
                                <i class="fas fa-check"></i> Simpan
                            </button>
                            <button class="btn btn-sm btn-secondary btn-cancel-status" data-id="{{ $p->id }}" title="Batal">
                                Batal
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-inbox"></i> Tidak ada data pemakaian
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $pemakaian->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
