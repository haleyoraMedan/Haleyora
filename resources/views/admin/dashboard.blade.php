@extends('layouts.admin')
@section('title', 'Dashboard Admin')

@section('content')
<div class="admin-card">
    <div class="admin-toolbar">
        <h2 class="admin-title"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h2>
    </div>

    <div class="row mt-3 g-3">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h6>Pengguna</h6>
                <div class="display-6 fw-bold">{{ $counts['users'] }}</div>
                <a href="{{ route('user.index') }}" class="stretched-link small">Kelola pengguna →</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h6>Mobil</h6>
                <div class="display-6 fw-bold">{{ $counts['mobils'] }}</div>
                <a href="{{ route('mobil.index') }}" class="stretched-link small">Kelola mobil →</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h6>Pemakaian Pending</h6>
                <div class="display-6 fw-bold">{{ $counts['pending_pemakaian'] }}</div>
                <a href="{{ route('admin.pemakaian.daftar') }}" class="stretched-link small">Lihat pemakaian →</a>
            </div>
        </div>
    </div>

</div>
@endsection
