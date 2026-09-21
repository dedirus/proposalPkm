<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Manajemen & Review Proposal PKM')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .card {
            border-radius: 10px;
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <i class="bi bi-mortarboard-fill fs-4"></i>
                <span>SIM-PKM</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-nav"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                @auth
                    <ul class="navbar-nav me-auto">
                        @if(auth()->user()->isMahasiswa())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.dashboard') ? 'active fw-bold' : '' }}" href="{{ route('mahasiswa.dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard Usulan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.proposal.create') ? 'active fw-bold' : '' }}" href="{{ route('mahasiswa.proposal.create') }}">
                                    <i class="bi bi-file-earmark-plus"></i> Ajukan Proposal
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->isDosen())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dosen.proposals.available') ? 'active fw-bold' : '' }}" href="{{ route('dosen.proposals.available') }}">
                                    <i class="bi bi-inbox-fill"></i> Proposal Tersedia (Claim)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dosen.bimbingan.*') ? 'active fw-bold' : '' }}" href="{{ route('dosen.bimbingan.index') }}">
                                    <i class="bi bi-journal-bookmark-fill"></i> Bimbingan Saya
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active fw-bold' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-grid-1x2-fill"></i> Monitoring Usulan PKM (Admin)
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.mahasiswa.*') || request()->routeIs('admin.dosen.*') ? 'active fw-bold' : '' }}" href="#" id="adminSettingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-gear-fill"></i> Setting
                                </a>
                                <ul class="dropdown-menu shadow-sm" aria-labelledby="adminSettingDropdown">
                                    <li class="dropdown-header text-uppercase small fw-bold text-muted" style="font-size: 0.72rem;">Kelola Mahasiswa</li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('admin.mahasiswa.create') ? 'active' : '' }}" href="{{ route('admin.mahasiswa.create') }}">
                                            <i class="bi bi-person-plus-fill text-primary me-2"></i> Add Mahasiswa
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('admin.mahasiswa.index') ? 'active' : '' }}" href="{{ route('admin.mahasiswa.index') }}">
                                            <i class="bi bi-people-fill text-secondary me-2"></i> Data Mahasiswa Terdaftar
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-header text-uppercase small fw-bold text-muted" style="font-size: 0.72rem;">Kelola Dosen Pembimbing</li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('admin.dosen.create') ? 'active' : '' }}" href="{{ route('admin.dosen.create') }}">
                                            <i class="bi bi-person-plus text-success me-2"></i> Add Dosen
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('admin.dosen.index') ? 'active' : '' }}" href="{{ route('admin.dosen.index') }}">
                                            <i class="bi bi-person-badge-fill text-success me-2"></i> Data Dosen Pembimbing
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>

                    <div class="d-flex align-items-center gap-3">
                        <div class="text-light text-end lh-sm">
                            <div class="fw-bold">{{ auth()->user()->name }}</div>
                            <small class="badge bg-light text-primary text-uppercase">{{ auth()->user()->role }} - {{ auth()->user()->nip_nim }}</small>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm" title="Logout">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 @yield('main-class', 'py-4')">
        <div class="@yield('container-class', 'container')">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <div class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-2"></i> Terjadi kesalahan input:</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-top py-3 mt-auto">
        <div class="container text-center text-muted small">
            &copy; {{ date('Y') }} Sistem Manajemen & Review Proposal PKM Mahasiswa &bull; ServBay Local Server
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
