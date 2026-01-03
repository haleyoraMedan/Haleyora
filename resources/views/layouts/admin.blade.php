<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Aplikasi Pemakaian Mobil')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #2f80ed;
            --orange: #ff8f00;
            --purple: #6a1b9a;
            --sidebar-bg: var(--purple);
            --muted: #6c757d;
            --border: #e5e7eb;
            --transition: all .3s ease;
        }

        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(145deg,#6a1b9a,#3a6edc,#ff8f00,#d1d1d1);
            overflow-x: hidden;
        }

        /* ===== WRAPPER ===== */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: #fff;
            padding: 20px 16px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: var(--transition);
        }

        .sidebar-brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo {
            width: 160px;
            max-width: 100%;
            padding: 8px;
            border-radius: 12px;
            background: linear-gradient(90deg, var(--primary), var(--purple));
            margin-bottom: 12px;
        }

        .sidebar-brand h5 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav-label {
            font-size: 12px;
            text-transform: uppercase;
            color: rgba(255,255,255,.6);
            margin: 20px 8px 10px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: rgba(255,255,255,.8);
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .sidebar-nav a i {
            width: 18px;
            text-align: center;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255,255,255,.08);
            color: #fff;
            box-shadow: inset 0 0 0 2px var(--primary);
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 280px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: var(--transition);
            max-width: 100vw;
        }

        /* ===== TOP NAVBAR ===== */
        .navbar-top {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            display: none;
        }

        .navbar-user {
            font-size: 14px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ===== CONTENT ===== */
        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .sidebar {
                width: 240px;
            }
            .main-content {
                margin-left: 240px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -280px;
            }

            .sidebar.show {
                left: 0;
                box-shadow: 2px 0 20px rgba(0,0,0,.3);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }

            .navbar-title {
                font-size: 16px;
            }

            .navbar-user span {
                display: none;
            }

            .content {
                padding: 14px;
            }
        }
        
       /* =====================================================
   GLOBAL RESPONSIVE TABLE (ADMIN)
   Berlaku untuk semua .table-responsive
===================================================== */

/* Wrapper: hanya tabel yang boleh scroll */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}

/* Tabel tidak memaksa layar */
.table-responsive > table {
    width: 100%;
    min-width: 1100px; /* penting: sesuaikan jumlah kolom */
    white-space: nowrap;
}

/* Header & cell tetap rapi */
.table-responsive th,
.table-responsive td {
    vertical-align: middle;
}

/* Scrollbar horizontal */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Mobile optimization */
@media (max-width: 768px) {
    .table-responsive > table {
        min-width: 1000px; /* mobile tetap bisa geser */
        font-size: 13px;
    }
}
      
    </style>
    @stack('styles')
</head>

<body>
<div class="admin-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('image/cabangmedan.png') }}" class="logo">
            <h5>Admin Panel</h5>
        </div>

        <ul class="sidebar-nav">
            <div class="sidebar-nav-label">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin/dashboard*') ? 'active' : '' }}">
                <i class="fas fa-home"></i> Dashboard
            </a>

            <div class="sidebar-nav-label">Data</div>
            <a href="{{ route('admin.pemakaian.daftar') }}"><i class="fas fa-clipboard-list"></i> Pemakaian</a>
            <a href="{{ route('mobil.index') }}"><i class="fas fa-car"></i> Mobil</a>
            <a href="{{ route('user.index') }}"><i class="fas fa-users"></i> User</a>

            <div class="sidebar-nav-label">Master</div>
            <a href="{{ route('penempatan.index') }}"><i class="fas fa-map-marker-alt"></i> Penempatan</a>
            <a href="{{ route('merek-mobil.index') }}"><i class="fas fa-tag"></i> Merek</a>
            <a href="{{ route('jenis-mobil.index') }}"><i class="fas fa-list"></i> Jenis</a>

            <div class="sidebar-nav-label">Account</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="all:unset;width:100%;">
                    <a style="cursor:pointer;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </button>
            </form>
        </ul>
    </aside>

    <!-- MAIN -->
    <div class="main-content">
        <nav class="navbar-top">
            <div class="navbar-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="navbar-title">@yield('title')</h1>
            </div>

            <div class="navbar-user">
                <i class="fas fa-user-circle"></i>
                <span>{{ auth()->user()->name ?? 'Admin' }}</span>
            </div>
        </nav>

        <div class="content">
            @yield('content')
        </div>
    </div>

</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
    });

    document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('show');
            }
        });
    });
</script>

@stack('scripts')
</body>
</html>