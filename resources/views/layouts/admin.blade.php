<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Aplikasi Pemakaian Mobil')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <div class="sidebar p-3" style="width:240px;">
        <h4 class="px-2">Admin</h4>
        <nav class="mt-3">
            <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.pemakaian.daftar') }}" class="{{ request()->is('admin/pemakaian*') ? 'active' : '' }}">Pemakaian</a>
            <a href="{{ route('mobil.index') }}" class="{{ request()->is('mobil*') ? 'active' : '' }}">Mobil</a>
            <a href="{{ route('merek-mobil.index') }}" class="{{ request()->is('merek-mobil*') ? 'active' : '' }}">Merek</a>
            <a href="{{ route('jenis-mobil.index') }}" class="{{ request()->is('jenis-mobil*') ? 'active' : '' }}">Jenis</a>
            <a href="{{ route('user.index') }}" class="{{ request()->is('user*') ? 'active' : '' }}">User</a>
        </nav>
    </div>

    <div class="flex-fill">
        <nav class="navbar navbar-expand navbar-light bg-light">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">@yield('title')</span>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3">{{ auth()->user()->name ?? '' }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary">Logout</button>
                    </form>
                </div>
            </div>
        </nav>

        <div class="content container-fluid">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
