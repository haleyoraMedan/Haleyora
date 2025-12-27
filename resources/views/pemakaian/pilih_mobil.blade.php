@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Pilih Mobil</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pemakaian.simpanPilihan') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Pilih Mobil</label>
            <select name="mobil_id" class="form-control" required>
                <option value="">-- Pilih Mobil --</option>
                @foreach($mobils as $mobil)
                    <option value="{{ $mobil->id }}" {{ $pilihanMobilId == $mobil->id ? 'selected' : '' }}>
                        {{ $mobil->no_polisi }} - {{ $mobil->merek->nama_merek ?? '' }} ({{ $mobil->tipe }})
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Pilih Mobil</button>
    </form>
</div>
@endsection
