<!DOCTYPE html>
<html>
<head>
    <title>Data Penempatan</title>
    <style>
        body { font-family: Arial; padding:20px; background:#f4f6f9; }
        .container { background:#fff; padding:20px; border-radius:6px; }
        table { width:100%; border-collapse: collapse; margin-top:15px; }
        th, td { border:1px solid #ddd; padding:8px; }
        th { background:#f1f1f1; }
        a.btn, button { padding:5px 10px; border-radius:4px; text-decoration:none; color:white; cursor:pointer; }
        .btn-add { background:#007bff; display:inline-block; margin-bottom:10px; }
        .btn-edit { background:#28a745; }
        .btn-delete { background:#dc3545; border:none; }
        form.inline { display:inline; }
        .form-container { background:#f9f9f9; padding:15px; border-radius:6px; margin-bottom:15px; width:500px; }
        label { display:block; margin-top:10px; }
        input { width:100%; padding:8px; margin-top:5px; }
        button.submit-btn { width:100%; margin-top:15px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Data Penempatan</h2>

    <!-- Form Tambah / Edit -->
    <div class="form-container">
        <h3>{{ isset($editPenempatan) ? 'Edit Penempatan' : 'Tambah Penempatan' }}</h3>
        <form action="{{ isset($editPenempatan) ? route('penempatan.update', $editPenempatan->id) : route('penempatan.store') }}" method="POST">
            @csrf
            @if(isset($editPenempatan))
                @method('PUT')
            @endif

            <label>Kode Kantor</label>
            <input type="text" name="kode_kantor" value="{{ $editPenempatan->kode_kantor ?? '' }}" required>

            <label>Nama Kantor</label>
            <input type="text" name="nama_kantor" value="{{ $editPenempatan->nama_kantor ?? '' }}" required>

            <label>Alamat</label>
            <input type="text" name="alamat" value="{{ $editPenempatan->alamat ?? '' }}" required>

            <label>Kota</label>
            <input type="text" name="kota" value="{{ $editPenempatan->kota ?? '' }}" required>

            <label>Provinsi</label>
            <input type="text" name="provinsi" value="{{ $editPenempatan->provinsi ?? '' }}" required>

            <button type="submit" class="submit-btn" style="background: {{ isset($editPenempatan) ? '#28a745' : '#007bff' }};">
                {{ isset($editPenempatan) ? 'Update' : 'Simpan' }}
            </button>
        </form>
    </div>

    <!-- Search -->
    <form action="{{ route('penempatan.index') }}" method="GET">
        <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari penempatan...">
        <button type="submit">Cari</button>
    </form>

    <!-- Tabel -->
    <table>
        <tr>
            <th>No</th>
            <th>Kode Kantor</th>
            <th>Nama Kantor</th>
            <th>Alamat</th>
            <th>Kota</th>
            <th>Provinsi</th>
            <th>Aksi</th>
        </tr>
        @forelse($penempatan as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->kode_kantor }}</td>
            <td>{{ $item->nama_kantor }}</td>
            <td>{{ $item->alamat }}</td>
            <td>{{ $item->kota }}</td>
            <td>{{ $item->provinsi }}</td>
            <td>
                <a href="{{ route('penempatan.index', ['edit' => $item->id]) }}" class="btn btn-edit">Edit</a>
                <form action="{{ route('penempatan.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-delete">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7">Data tidak ditemukan</td></tr>
        @endforelse
    </table>
</div>
</body>
</html>
