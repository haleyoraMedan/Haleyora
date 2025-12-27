@extends('layouts.admin')

@section('title', 'Data Jenis Mobil')

@section('content')
<div class="card">
    <div class="card-body">
        <h3 class="card-title">Data Jenis Mobil</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($user->role === 'admin')
        <form action="{{ route('jenis-mobil.store') }}" method="POST" class="row g-2 mb-3">
            @csrf
            <div class="col-md-9">
                <input type="text" name="nama_jenis" class="form-control" placeholder="Nama jenis" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100">Simpan</button>
            </div>
        </form>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">No</th>
                        <th>Nama Jenis</th>
                        <th style="width:220px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->nama_jenis }}</td>
                        <td>
                            @if($user->role === 'admin')
                            <form action="{{ route('jenis-mobil.update', $item->id) }}" method="POST" class="d-flex gap-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nama_jenis" value="{{ $item->nama_jenis }}" class="form-control" required>
                                <button class="btn btn-success">Update</button>
                            </form>
                            <form action="{{ route('jenis-mobil.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?')" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger">Hapus</button>
                            </form>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection