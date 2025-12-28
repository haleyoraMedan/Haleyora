<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aplikasi Pemakaian Mobil')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS / Theme -->
    <style>
        :root{
            --primary:#2f80ed; /* blue */
            --accent-orange:#ff8f00; /* orange */
            --accent-purple:#6a1b9a; /* purple */
            --on-primary:#ffffff;
            --bg:#f4f6f9;
        }

        body {
            background: linear-gradient(135deg, rgba(47,128,237,0.06), rgba(106,27,154,0.03) 40%, rgba(255,143,0,0.03) 70%), var(--bg);
            min-height: 100vh;
            color: #0f172a;
        }

        .navbar { background: linear-gradient(90deg, var(--primary), var(--accent-purple)); }
        .navbar .nav-link, .navbar .navbar-brand { color: var(--on-primary) !important; }

        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--accent-orange); border-color: var(--accent-orange); }

        .navbar-brand { font-weight: bold; }
        .card { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .container { padding-top: 30px; padding-bottom: 30px; }
    </style>

    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('pemakaian.daftar') }}">SkyCar</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pemakaian.daftar') }}">Daftar Pemakaian</a>
                    </li>
                    <li class="nav-item">
                        
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
