<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Aplikasi Pemakaian Mobil')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height:100vh; }
        .sidebar { background:#2c3e50; color:#fff; min-height:100vh; }
        .sidebar a { color: #ddd; text-decoration:none; display:block; padding:10px 15px; }
        .sidebar a.active, .sidebar a:hover { background:#1a252f; color:#fff; }
        .content { padding:20px; }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <div class="sidebar p-2" style="width:220px;">
        <h4 class="px-3">Admin</h4>
        <nav class="mt-3">
            <a href="{{ url('/') }}">Dashboard</a>
            <a href="{{ route('admin.pemakaian.daftar') }}" class="active">Pemakaian</a>
            <a href="{{ route('mobil.index') }}">Mobil</a>
            <a href="{{ route('merek-mobil.index') }}">Merek</a>
            <a href="{{ route('jenis-mobil.index') }}">Jenis</a>
            <a href="{{ route('user.index') }}">User</a>
        </nav>
    </div>

    <div class="flex-fill">
        <nav class="navbar navbar-expand navbar-light bg-light">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">@yield('title')</span>
                <div class="ms-auto">
                    <span class="me-3">{{ auth()->user()->name ?? '' }}</span>
                    <a href="/logout" class="btn btn-sm btn-outline-secondary">Logout</a>
                </div>
            </div>
        </nav>

        <div class="content">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
