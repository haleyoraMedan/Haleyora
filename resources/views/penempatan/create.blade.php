@extends('layouts.admin')

@section('title', 'Tambah Penempatan')

@section('content')
<div class="admin-card" style="max-width: 600px;">
    <h3 class="admin-title mb-4">Tambah Penempatan</h3>

    <form action="{{ route('penempatan.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Kode Kantor</label>
            <input type="text" name="kode_kantor" class="form-control @error('kode_kantor') is-invalid @enderror" required>
            @error('kode_kantor') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Kantor</label>
            <input type="text" name="nama_kantor" class="form-control @error('nama_kantor') is-invalid @enderror" required>
            @error('nama_kantor') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" required></textarea>
            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Kota/Kabupaten</label>
                    <input type="text" name="kota" class="form-control @error('kota') is-invalid @enderror" required>
                    @error('kota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Provinsi</label>
                    <input type="text" name="provinsi" class="form-control @error('provinsi') is-invalid @enderror" required>
                    @error('provinsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="admin-btn primary">Simpan Penempatan</button>
            <a href="{{ route('penempatan.index') }}" class="admin-btn">Batal</a>
        </div>
    </form>
</div>
@endsection
