<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Aplikasi Pemakaian')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f7f9fc; }
        .navbar-brand { font-weight:700; }
        .content-wrapper { padding: 30px; }
        .card { border: none; box-shadow: 0 1px 6px rgba(0,0,0,0.08); }
    </style>
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">Haleyora</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="{{ route('pemakaian.daftar') }}">Pemakaian</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('pemakaian.pilihMobil') }}">Buat Pemakaian</a></li>
        <li class="nav-item">
            <form action="/logout" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-link nav-link" style="display:inline;padding:0">Logout</button>
            </form>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main class="content-wrapper container">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
