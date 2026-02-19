<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CMS Admin - Mudik Gratis')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; }
        .sidebar { position: fixed; top: 0; left: 0; width: 260px; height: 100vh; background: linear-gradient(180deg, #1B5E20 0%, #2E7D32 100%); color: white; padding: 20px 0; z-index: 1000; overflow-y: auto; }
        .sidebar-brand { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.2); margin-bottom: 20px; }
        .sidebar-brand h4 { font-weight: 700; font-size: 1.3rem; margin: 0; }
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu-item { margin-bottom: 5px; }
        .sidebar-menu-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: rgba(255,255,255,0.9); text-decoration: none; transition: all 0.3s; border-left: 3px solid transparent; }
        .sidebar-menu-link:hover, .sidebar-menu-link.active { background: rgba(255,255,255,0.15); color: white; border-left-color: white; }
        .main-content { margin-left: 260px; min-height: 100vh; }
        .topbar { background: white; padding: 15px 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; }
        .page-content { padding: 30px; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-toggle { display: block !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h4 class="heading-font">MUDIK ADMIN</h4>
            <small>Lebaran 2026</small>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="{{ route('cms.dashboard') }}" class="sidebar-menu-link {{ request()->routeIs('cms.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            @if(auth('admin')->user()->isSuperAdmin())
            <li class="sidebar-menu-item">
                <a href="{{ route('cms.email-requests.index') }}" class="sidebar-menu-link {{ request()->routeIs('cms.email-requests.*') ? 'active' : '' }}">
                    <i class="bi bi-envelope-check"></i>
                    <span>Email Requests</span>
                </a>
            </li>
            @endif
            <li class="sidebar-menu-item">
                <a href="{{ route('cms.registrations.index') }}" class="sidebar-menu-link {{ request()->routeIs('cms.registrations.*') ? 'active' : '' }}">
                    <i class="bi bi-file-text"></i>
                    <span>Pendaftaran</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('cms.destinations.index') }}" class="sidebar-menu-link {{ request()->routeIs('cms.destinations.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i>
                    <span>Kuota Tujuan</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('cms.quotas.index') }}" class="sidebar-menu-link {{ request()->routeIs('cms.quotas.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i>
                    <span>Kuota Harian</span>
                </a>
            </li>
            @can('scan', App\Models\QrCode::class)
            <li class="sidebar-menu-item">
                <a href="{{ route('cms.scanner.index') }}" class="sidebar-menu-link {{ request()->routeIs('cms.scanner.index') ? 'active' : '' }}">
                    <i class="bi bi-qr-code-scan"></i>
                    <span>Scanner QR</span>
                </a>
            </li>
            @endcan
            <li class="sidebar-menu-item">
                <a href="{{ route('cms.scanner.dashboard') }}" class="sidebar-menu-link {{ request()->routeIs('cms.scanner.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart"></i>
                    <span>Scan Dashboard</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <button class="btn btn-link mobile-toggle d-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="mb-0 d-inline-block">@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="dropdown">
                <button class="btn btn-link text-dark dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5 me-2"></i>
                    {{ auth('admin')->user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text small">{{ auth('admin')->user()->email }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-content">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('show');
        });
    </script>
    @stack('scripts')
</body>
</html>
