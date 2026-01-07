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
            --transition: all 0.25s ease;
        }

        .theme-orange {
            --primary-color: #ff8f00;
            --primary-dark: #c46a00;
            --bg-light: #fff7ed;
        }

        .theme-purple {
            --primary-color: #6a1b9a;
            --primary-dark: #4a116e;
            --bg-light: #f5f3ff;
        }

        .theme-blue {
            --primary-color: #3a6edc;
            --primary-dark: #2c54a8;
            --bg-light: #eef2ff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(145deg,#6a1b9a,#3a6edc,#ff8f00,#d1d1d1);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial;
            padding-bottom: 50px;
        }

        .pegawai-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* NAVBAR */
        .navbar-pegawai {
            background: #fff;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1400px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .navbar-brand-pegawai {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .navbar-logo {
            width: 160px;
            max-height: 160px;
            object-fit: contain;
        }

        .navbar-toggler {
            background: none;
            border: none;
            font-size: 22px;
            display: none;
        }

        .navbar-menu {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 0;
        }

        .navbar-menu a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 6px;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 20px;
            text-decoration: none;
            transition: var(--transition);
        }

        .navbar-menu a:hover {
            background: var(--bg-light);
            color: var(--primary-color);
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 1px solid var(--border-color);
            padding-left: 12px;
        }

        .navbar-user-name {
            font-size: 14px;
            color: var(--text-muted);
        }

        .btn-logout {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 14px;
            transition: var(--transition);
        }

        .btn-logout:hover {
            background: #dc2626;
        }

        /* THEME BUTTON */
        .theme-switcher {
            display: flex;
            gap: 6px;
        }

        .theme-btn {
            width: 24px;
            height: 24px;
            border-radius: 5px;
            border: 2px solid rgba(0,0,0,.1);
            cursor: pointer;
        }

        /* CONTENT */
        .content-wrapper {
            flex: 1;
            padding: 24px;
            max-width: 1400px;
            margin: auto;
            width: 100%;
        }

        .card {
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        /* BADGE */
        .badge { color: #000; }
        .badge-pending { background:#ffeeba; }
        .badge-approved { background:#c3e6cb; }
        .badge-available { background:#bee5eb; }
        .badge-rejected { background:#f5c6cb; }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .navbar-toggler {
                display: block;
            }

            .navbar-container {
                flex-wrap: wrap;
            }

            .navbar-menu {
                width: 100%;
                flex-direction: column;
                display: none;
                border-top: 1px solid var(--border-color);
                margin-top: 12px;
            }

            .navbar-menu.show {
                display: flex;
            }

            .navbar-menu a {
                width: 100%;
                padding: 12px 16px;
            }

            .navbar-user {
                width: 100%;
                border-left: none;
                border-top: 1px solid var(--border-color);
                padding-top: 12px;
                margin-top: 12px;
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-logout {
                width: 100%;
            }

            .content-wrapper {
                padding: 12px;
            }
        }

        @media (max-width: 576px) {
            .navbar-logo {
                width: 100px;
            }
        }

        .fixed-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #0d6dfd42; /* biru bootstrap */
            color: black;
            text-align: center;
            padding: 10px 0;
            font-size: 14px;
            z-index: 999;
        }

    </style>
    @stack('styles')
</head>

<body>
<div class="pegawai-wrapper">
    <nav class="navbar-pegawai">
        <div class="navbar-container">
            <a href="/" class="navbar-brand-pegawai">
                <img src="{{ asset('image/hpi-cabangmedan.png') }}" class="navbar-logo">
            </a>

            <button class="navbar-toggler" id="navToggle">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="navbar-menu">
                <li><a href="{{ route('pemakaian.daftar') }}"><i class="fas fa-list"></i> Daftar Pemakaian</a></li>
                <li><a href="{{ route('pemakaian.pilihMobil') }}"><i class="fas fa-plus-circle"></i> Buat Pemakaian</a></li>
                <li><a href="{{ route('pegawai.mobilRusak') }}"><i class="fas fa-plus-circle"></i> Mobil Rusak</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <div class="theme-switcher">
                    <div class="theme-btn" data-theme="theme-orange" style="background:#ff8f00"></div>
                    <div class="theme-btn" data-theme="theme-purple" style="background:#6a1b9a"></div>
                    <div class="theme-btn" data-theme="theme-blue" style="background:#3a6edc"></div>
                </div>

                <div class="navbar-user">
                    <span class="navbar-user-name">
                        <i class="fas fa-user-circle"></i>
                        {{ auth()->user()->name ?? auth()->user()->username ?? 'Pegawai' }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="content-wrapper">
        @yield('content')
    </main>
</div>

<footer class="fixed-footer">
    © 2026 Haleyora × SMKN 9 Medan — Partnership Project
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('navToggle')?.addEventListener('click', () => {
        document.querySelector('.navbar-menu').classList.toggle('show');
    });

    (function(){
        const key = 'pegawaiTheme';
        const apply = t => {
            document.body.classList.remove('theme-orange','theme-purple','theme-blue');
            if(t) document.body.classList.add(t);
        };

        apply(localStorage.getItem(key));

        document.querySelectorAll('.theme-btn').forEach(b => {
            b.onclick = () => {
                apply(b.dataset.theme);
                localStorage.setItem(key, b.dataset.theme);
            };
        });
    })();
</script>

@stack('scripts')
</body>
</html>