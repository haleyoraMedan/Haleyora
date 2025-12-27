@extends('layouts.admin')

@section('title', 'Data Mobil')

@section('content')
    <style>

        .container {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 { margin-bottom: 20px; color: #333; }
        h3 { margin-bottom: 15px; color: #555; }
        form { margin-bottom: 30px; }
        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }
        label { margin-bottom: 6px; font-weight: 600; color: #555; }
        input, select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            transition: border 0.3s;
        }
        input:focus, select:focus { border-color: #007bff; outline: none; }
        button.save-btn {
            background: #007bff;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        button.save-btn:hover { background: #0056b3; }

        .btn {
            display: inline-block;
            padding: 6px 14px;
            text-decoration: none;
            border-radius: 6px;
            color: #fff;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .btn-add { background: #28a745; }
        .btn-edit { background: #17a2b8; }
        .btn-delete { background: #dc3545; border: none; cursor: pointer; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #f1f1f1; color: #333; }

        .btn-detail {
            background: #6c757d;
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            cursor: pointer;
        }

        /* Modal */
        .modal {
            display:none;
            position:fixed;
            z-index:999;
            left:0;
            top:0;
            width:100%;
            height:100%;
            overflow:auto;
            background:rgba(0,0,0,0.4);
        }
        .modal-content {
            background:#fff;
            margin:10% auto;
            padding:20px;
            border-radius:8px;
            width:420px;
            position:relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .close {
            color:#aaa;
            position:absolute;
            top:10px;
            right:15px;
            font-size:28px;
            font-weight:bold;
            cursor:pointer;
        }
        .close:hover { color:black; }
        .modal label { font-weight:600; margin-top:10px; display:block; }
    </style>    
<div class="container">

    <h2>Data Mobil</h2>

    <!-- Form Tambah / Edit -->
    @if(isset($penempatans) && isset($merek) && isset($jenis))
    <h3>{{ isset($mobil) ? 'Edit Mobil' : 'Tambah Mobil' }}</h3>
    <form action="{{ isset($mobil) ? route('mobil.update', $mobil->id) : route('mobil.store') }}" method="POST">
        @csrf
        @if(isset($mobil)) @method('PUT') @endif

        <div class="form-group">
            <label>No Polisi</label>
            <input type="text" name="no_polisi" value="{{ $mobil->no_polisi ?? '' }}" required>
        </div>

        <div class="form-group">
            <label>Merek</label>
            <select name="merek_id" required>
                <option value="">-- Pilih Merek --</option>
                @foreach($merek as $m)
                    <option value="{{ $m->id }}" {{ isset($mobil) && $mobil->merek_id == $m->id ? 'selected' : '' }}>
                        {{ $m->nama_merek }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Jenis</label>
            <select name="jenis_id" required>
                <option value="">-- Pilih Jenis --</option>
                @foreach($jenis as $j)
                    <option value="{{ $j->id }}" {{ isset($mobil) && $mobil->jenis_id == $j->id ? 'selected' : '' }}>
                        {{ $j->nama_jenis }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Tipe</label>
            <input type="text" name="tipe" value="{{ $mobil->tipe ?? '' }}" required>
        </div>

        <div class="form-group">
            <label>Tahun</label>
            <input type="number" name="tahun" value="{{ $mobil->tahun ?? '' }}" required>
        </div>

        <div class="form-group">
            <label>Warna</label>
            <input type="text" name="warna" value="{{ $mobil->warna ?? '' }}" required>
        </div>

        <div class="form-group">
            <label>No Rangka</label>
            <input type="text" name="no_rangka" value="{{ $mobil->no_rangka ?? '' }}" required>
        </div>

        <div class="form-group">
            <label>No Mesin</label>
            <input type="text" name="no_mesin" value="{{ $mobil->no_mesin ?? '' }}" required>
        </div>

        <div class="form-group">
            <label>Penempatan</label>
            <select name="penempatan_id">
                <option value="">-- Pilih Penempatan --</option>
                @foreach($penempatans as $p)
                    <option value="{{ $p->id }}" {{ isset($mobil) && $mobil->penempatan_id == $p->id ? 'selected' : '' }}>
                        {{ $p->nama_kantor }} - {{ $p->kota }}, {{ $p->provinsi }} - {{ $p->alamat }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="save-btn">{{ isset($mobil) ? 'Update' : 'Simpan' }}</button>
    </form>
    @endif

    <!-- Form Pencarian -->
    <form method="GET" action="{{ route('mobil.index') }}" style="margin-bottom:15px; display:flex; gap:10px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Cari No Polisi, Merek, Tipe..." value="{{ request('search') }}" style="flex:1; padding:8px; border-radius:6px; border:1px solid #ccc;">
        <button type="submit" class="save-btn" style="padding:8px 16px;">Cari</button>
    </form>

    <!-- Tombol tambah -->
    <a href="{{ route('mobil.create') }}" class="btn btn-add">+ Tambah Mobil</a>

    <!-- Tabel Index -->
    @if(isset($mobils))
    <table>
        <tr>
            <th>No</th>
            <th>No Polisi</th>
            <th>Merek</th>
            <th>Jenis</th>
            <th>Tipe</th>
            <th>Tahun</th>
            <th>Warna</th>
            <th>No Rangka</th>
            <th>No Mesin</th>
            <th>Penempatan</th>
            <th>Aksi</th>
        </tr>
        @forelse($mobils as $mobil)
        <tr>
            <td>{{ $loop->iteration + ($mobils->currentPage()-1) * $mobils->perPage() }}</td>
            <td>{{ $mobil->no_polisi }}</td>
            <td>{{ $mobil->merek->nama_merek ?? '-' }}</td>
            <td>{{ $mobil->jenis->nama_jenis ?? '-' }}</td>
            <td>{{ $mobil->tipe }}</td>
            <td>{{ $mobil->tahun }}</td>
            <td>{{ $mobil->warna }}</td>
            <td>{{ $mobil->no_rangka }}</td>
            <td>{{ $mobil->no_mesin }}</td>
            <td>
                @if($mobil->penempatan)
                    {{ $mobil->penempatan->nama_kantor }}
                    <button class="btn-detail" onclick="openModal({{ $mobil->penempatan->id }}, '{{ $mobil->penempatan->kode_kantor }}', '{{ $mobil->penempatan->alamat }}', '{{ $mobil->penempatan->kota }}', '{{ $mobil->penempatan->provinsi }}')">Detail</button>
                @else
                    -
                @endif
            </td>
            <td>
                <a href="{{ route('mobil.edit', $mobil->id) }}" class="btn btn-edit">Edit</a>
                @if(!$mobil->is_deleted)
                <form action="{{ route('mobil.destroy', $mobil->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus mobil ini?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn-delete">Hapus</button>
                </form>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="11">Data tidak ditemukan</td>
        </tr>
        @endforelse
    </table>

    <!-- Pagination -->
    <div style="margin-top:15px;">
        {{ $mobils->appends(request()->query())->links() }}
    </div>
    @endif

</div>

<!-- Modal Detail Penempatan -->
<div id="penempatanModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Detail Penempatan</h3>
        <label>ID:</label> <span id="modal-id"></span>
        <label>Kode Kantor:</label> <span id="modal-kode"></span>
        <label>Alamat:</label> <span id="modal-alamat"></span>
        <label>Kota:</label> <span id="modal-kota"></span>
        <label>Provinsi:</label> <span id="modal-provinsi"></span>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openModal(id, kode, alamat, kota, provinsi) {
        document.getElementById('modal-id').innerText = id;
        document.getElementById('modal-kode').innerText = kode;
        document.getElementById('modal-alamat').innerText = alamat;
        document.getElementById('modal-kota').innerText = kota;
        document.getElementById('modal-provinsi').innerText = provinsi;
        document.getElementById('penempatanModal').style.display = 'block';
    }
    function closeModal() {
        document.getElementById('penempatanModal').style.display = 'none';
    }
    window.onclick = function(event) {
        let modal = document.getElementById('penempatanModal');
        if (event.target == modal) { modal.style.display = "none"; }
    }
</script>
@endsection
