<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Aplikasi Pemakaian Mobil')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/admin.css') }}" rel="stylesheet">
    <style>
        :root {
            --admin-bg: #f8f9fa;
            --sidebar-bg: #2c3e50;
            --sidebar-hover: #1a252f;
            --accent: #4f46e5;
            --muted: #6c757d;
            --transition: all 0.3s ease;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        html, body { height: 100%; }
        
        body { 
            background: var(--admin-bg); 
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            overflow-x: hidden;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: #fff;
            padding: 24px 16px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand i {
            font-size: 24px;
            color: var(--accent);
        }
        
        .sidebar-brand h5 {
            margin: 0;
            font-weight: 700;
            font-size: 18px;
        }
        
        .sidebar-nav {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .sidebar-nav-section {
            margin-bottom: 28px;
        }
        
        .sidebar-nav-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            margin-bottom: 12px;
            padding: 0 8px;
            letter-spacing: 0.5px;
        }
        
        .sidebar-nav li {
            margin-bottom: 8px;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 500;
            font-size: 14px;
        }
        
        .sidebar-nav a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: var(--sidebar-hover);
            color: #fff;
            box-shadow: inset 0 0 0 2px var(--accent);
        }
        
        .main-content {
            margin-left: 280px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .navbar-top {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .navbar-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .navbar-user-name {
            color: #6c757d;
            font-size: 14px;
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
        }
        
        .btn-logout:hover {
            background: #dc2626;
        }
        
        .content {
            flex: 1;
            padding: 24px 32px;
            overflow-y: auto;
        }
        
        .admin-card {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        
        .admin-toolbar {
            display: flex;
            gap: 16px;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        
        .admin-title {
            font-weight: 700;
            color: #111827;
            font-size: 24px;
            margin: 0;
        }
        
        .admin-btn {
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-btn.primary {
            background: var(--accent);
            color: #fff;
        }
        
        .admin-btn.primary:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }
        
        .admin-btn.success {
            background: #10b981;
            color: #fff;
        }
        
        .admin-btn.success:hover {
            background: #059669;
        }
        
        .admin-btn.danger {
            background: #ef4444;
            color: #fff;
        }
        
        .admin-btn.danger:hover {
            background: #dc2626;
        }
        
        .table-admin {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        
        .table-admin table {
            margin-bottom: 0;
        }
        
        .form-admin .form-control,
        .form-admin .form-select {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            font-size: 14px;
        }
        
        .form-admin .form-control:focus,
        .form-admin .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--muted);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #999;
        }
        
        /* Mobile responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 240px;
                padding: 16px;
            }
            
            .main-content {
                margin-left: 240px;
            }
            
            .navbar-top {
                padding: 12px 20px;
            }
            
            .content {
                padding: 16px 20px;
            }
            
            .navbar-title {
                font-size: 18px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -280px;
                transition: var(--transition);
                width: 280px;
                border-right: 1px solid rgba(0,0,0,0.1);
            }
            
            .sidebar.show {
                left: 0;
                box-shadow: 2px 0 20px rgba(0,0,0,0.2);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-toggle {
                display: flex;
                background: none;
                border: none;
                color: #111827;
                font-size: 20px;
                cursor: pointer;
            }
            
            .navbar-top {
                padding: 12px 16px;
            }
            
            .content {
                padding: 12px 16px;
            }
            
            .admin-card {
                padding: 16px;
            }
            
            .navbar-title {
                font-size: 16px;
            }
            
            .navbar-user-name {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-car"></i>
            <h5>Admin Panel</h5>
        </div>
        
        <ul class="sidebar-nav">
            <!-- Dashboard -->
            <li class="sidebar-nav-section">
                <div class="sidebar-nav-label">Main</div>
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin/dashboard*') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('mobil.pegawai.index') }}" class="{{ request()->is('pegawai/mobil*') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                @endif
            </li>
            
            <!-- Data Management -->
            <li class="sidebar-nav-section">
                <div class="sidebar-nav-label">Data Management</div>
                <a href="{{ route('admin.pemakaian.daftar') }}" class="{{ request()->is('admin/pemakaian*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Pemakaian Mobil</span>
                </a>
                <a href="{{ route('mobil.index') }}" class="{{ request()->is('mobil*') ? 'active' : '' }}">
                    <i class="fas fa-car"></i>
                    <span>Mobil</span>
                </a>
                <a href="{{ route('user.index') }}" class="{{ request()->is('user*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>User</span>
                </a>
            </li>
            
            <!-- Master Data -->
            <li class="sidebar-nav-section">
                <div class="sidebar-nav-label">Master Data</div>
                <a href="{{ route('penempatan.index') }}" class="{{ request()->is('penempatan*') ? 'active' : '' }}">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Penempatan</span>
                </a>
                <a href="{{ route('merek-mobil.index') }}" class="{{ request()->is('merek-mobil*') ? 'active' : '' }}">
                    <i class="fas fa-tag"></i>
                    <span>Merek Mobil</span>
                </a>
                <a href="{{ route('jenis-mobil.index') }}" class="{{ request()->is('jenis-mobil*') ? 'active' : '' }}">
                    <i class="fas fa-list"></i>
                    <span>Jenis Mobil</span>
                </a>
            </li>
            
            <!-- Tools -->
            <li class="sidebar-nav-section">
                <div class="sidebar-nav-label">Tools</div>
                <a href="{{ route('admin.tools.importExport') }}" class="{{ request()->is('admin/tools*') ? 'active' : '' }}">
                    <i class="fas fa-download"></i>
                    <span>Import/Export</span>
                </a>
            </li>
            
            <!-- Account -->
            <li class="sidebar-nav-section">
                <div class="sidebar-nav-label">Account</div>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="width: 100%; background: none; border: none; padding: 0;" 
                            onclick="event.target.closest('form').submit(); return false;">
                        <a style="display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,0.7); text-decoration: none; padding: 12px 16px; border-radius: 8px; transition: var(--transition); font-weight: 500; font-size: 14px; cursor: pointer;">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </button>
                </form>
            </li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar-top">
            <div style="display: flex; align-items: center; gap: 16px;">
                <button class="sidebar-toggle" id="sidebarToggle" style="display: none;">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="navbar-title">@yield('title')</h1>
            </div>
            
            <div class="navbar-user">
                <span class="navbar-user-name">
                    <i class="fas fa-user-circle" style="margin-right: 6px;"></i>
                    {{ auth()->user()->name ?? auth()->user()->username ?? 'User' }}
                </span>
            </div>
        </nav>
        
        <!-- Content Area -->
        <div class="content">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Mobile sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    
    if (window.innerWidth <= 768) {
        toggle.style.display = 'flex';
    }
    
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            toggle.style.display = 'none';
            sidebar.classList.remove('show');
        } else {
            toggle.style.display = 'flex';
        }
    });
    
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
    });
    
    // Close sidebar when link clicked on mobile
    document.querySelectorAll('.sidebar-nav a').forEach(link => {
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
