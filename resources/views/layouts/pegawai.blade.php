<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Aplikasi Pemakaian Mobil')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
            --bg-light: #f8f9fa;
            --text-dark: #111827;
            --text-muted: #6c757d;
            --border-color: #e5e7eb;
            --transition: all 0.3s ease;
        }

        /* Theme classes: orange, purple, blue */
        .theme-orange {
            --primary-color: #ff8f00;
            --primary-dark: #c46a00;
            --bg-light: #fff7ed;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --border-color: #f3e8d8;
        }

        .theme-purple {
            --primary-color: #6a1b9a;
            --primary-dark: #4a116e;
            --bg-light: #f5f3ff;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --border-color: #ede9fe;
        }

        .theme-blue {
            --primary-color: #3a6edc;
            --primary-dark: #2c54a8;
            --bg-light: #eef2ff;
            --text-dark: #0f172a;
            --text-muted: #475569;
            --border-color: #e6eefc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            background: linear-gradient(145deg,
                #6a1b9a 0%,
                #3a6edc 25%,
                #ff8f00 50%,
                #d1d1d1 100%
            ); 
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--text-dark);
        }

        .pegawai-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Navbar */
        .navbar-pegawai {
            background: #fff;
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 16px 32px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* Theme selector styles */
        .theme-switcher {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            margin-right: 12px;
        }

        .theme-btn {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 2px solid rgba(0,0,0,0.06);
            cursor: pointer;
            transition: transform 0.15s ease;
        }

        .theme-btn:active { transform: scale(0.96); }

        .navbar-brand-pegawai {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-dark);
        }

        .navbar-brand-pegawai i {
            font-size: 28px;
            color: var(--primary-color);
        }

        .navbar-logo {
            width: 150px;
            height: 150px;
            object-fit: contain;
            display: inline-block;
        }

        .navbar-brand-pegawai h4 {
            margin: 0;
            font-weight: 700;
            font-size: 30px;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
            margin: 0;
        }

        .navbar-menu li {
            margin: 0;
        }

        .navbar-menu a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 6px;
            transition: var(--transition);
            font-weight: 500;
            font-size: 25px;
        }

        .navbar-menu a:hover {
            background: var(--bg-light);
            color: var(--primary-color);
        }

        .navbar-menu a i {
            font-size: 25px;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: 16px;
            padding-left: 16px;
            border-left: 1px solid var(--border-color);
        }

        .navbar-user-name {
            color: var(--text-muted);
            font-size: 25px;
            font-weight: 500;
        }

        .btn-logout {
            padding: 8px 16px;
            background: #ef4444;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Content Area */
        .content-wrapper {
            flex: 1;
            padding: 32px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .card {
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            background: #fff;
        }

        .card-header {
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            padding: 20px 24px;
            border-radius: 12px 12px 0 0;
        }

        .card-body {
            padding: 24px;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 6px;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid var(--border-color);
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-dark);
            padding: 12px 16px;
            font-size: 14px;
        }

        .table tbody td {
            padding: 12px 16px;
            border-color: var(--border-color);
            vertical-align: middle;
        }

        .alert {
            border-radius: 8px;
            border: 1px solid;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #ecfdf5;
            border-color: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background: #fef2f2;
            border-color: #fee2e2;
            color: #991b1b;
        }

        .alert-info {
            background: #f0f9ff;
            border-color: #e0f2fe;
            color: #0c4a6e;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
        }

        .form-control,
        .form-select {
            border-radius: 6px;
            border: 1px solid var(--border-color);
            padding: 10px 12px;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--text-muted);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .navbar-pegawai {
                padding: 12px 16px;
            }

            .navbar-container {
                flex-wrap: wrap;
            }

            .navbar-menu {
                flex-direction: column;
                width: 100%;
                gap: 4px;
                margin-top: 12px;
            }

            .navbar-user {
                flex-direction: column;
                width: 100%;
                border-left: none;
                border-top: 1px solid var(--border-color);
                margin-left: 0;
                padding-left: 0;
                padding-top: 12px;
                margin-top: 12px;
            }

            .navbar-user-name {
                width: 100%;
            }

            .btn-logout {
                width: 100%;
                justify-content: center;
            }

            .content-wrapper {
                padding: 16px;
            }

            .card-body,
            .card-header {
                padding: 16px;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand-pegawai h4 {
                font-size: 16px;
            }

            .navbar-menu a {
                padding: 8px 12px;
                font-size: 13px;
            }

            .content-wrapper {
                padding: 12px;
            }

            .table {
                font-size: 13px;
            }

            .table thead th,
            .table tbody td {
                padding: 8px 10px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="pegawai-wrapper">
    <!-- Top Navbar -->
    <nav class="navbar-pegawai">
        <div class="navbar-container">
            <a href="/" class="navbar-brand-pegawai">
                <img src="{{ asset('image/hpi-cabangmedan.png') }}" alt="Haleyora" class="navbar-logo">
                <!-- <h4>Haleyora</h4> -->
            </a>

            <ul class="navbar-menu">
                <li><a href="{{ route('pemakaian.daftar') }}"><i class="fas fa-list"></i> Daftar Pemakaian</a></li>
                <li><a href="{{ route('pemakaian.pilihMobil') }}"><i class="fas fa-plus-circle"></i> Buat Pemakaian</a></li>
            </ul>

            <div style="display:flex;align-items:center;gap:10px;">
                <div class="theme-switcher" aria-label="Pilih Tema">
                    <button class="theme-btn" data-theme="theme-orange" title="Orange" style="background:#ff8f00"></button>
                    <button class="theme-btn" data-theme="theme-purple" title="Purple" style="background:#6a1b9a"></button>
                    <button class="theme-btn" data-theme="theme-blue" title="Blue" style="background:#3a6edc"></button>
                </div>

                <div class="navbar-user">
                <span class="navbar-user-name">
                    <i class="fas fa-user-circle"></i> {{ auth()->user()->name ?? auth()->user()->username ?? 'Pegawai' }}
                </span>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="content-wrapper">
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function(){
        const storageKey = 'pegawaiTheme';
        const applyTheme = (theme) => {
            document.body.classList.remove('theme-orange','theme-purple','theme-blue');
            if(theme) document.body.classList.add(theme);
        };

        // Apply saved theme on load
        const saved = localStorage.getItem(storageKey);
        if(saved) applyTheme(saved);

        // Hook up buttons
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.addEventListener('click', function(){
                const t = this.getAttribute('data-theme');
                applyTheme(t);
                localStorage.setItem(storageKey, t);
            });
        });
    })();
</script>
@stack('scripts')
</body>
</html>
