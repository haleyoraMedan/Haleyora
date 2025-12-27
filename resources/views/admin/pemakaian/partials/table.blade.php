<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Mobil</th>
            <th>Tujuan</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Selesai</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pemakaian as $p)
        <tr data-id="{{ $p->id }}">
            <td>{{ $p->id }}</td>
            <td>{{ $p->mobil->no_polisi }} - {{ $p->mobil->merek->nama_merek ?? '' }}</td>
            <td>{{ $p->tujuan }}</td>
            <td>{{ $p->tanggal_mulai }}</td>
            <td>{{ $p->tanggal_selesai ?? '-' }}</td>
            <td class="status-cell">{{ ucfirst($p->status) }}</td>
            <td>
                <button class="btn-detail" data-id="{{ $p->id }}">Detail</button>

                <select class="status-select" data-id="{{ $p->id }}">
                    <option value="pending" {{ $p->status=='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $p->status=='approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $p->status=='rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="available" {{ $p->status=='available' ? 'selected' : '' }}>Available</option>
                </select>
                <button class="btn-update-status" data-id="{{ $p->id }}">Ubah</button>
                <button class="btn-batal" data-id="{{ $p->id }}">Batal</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="pagination-links">
    {{ $pemakaian->links() }}
</div>
