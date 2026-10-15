@extends('layouts.app')

@section('title', 'Data Administrator Terdaftar - Setting SIM PKM')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-shield-lock-fill text-danger me-2"></i>Data Administrator Kampus
            </h3>
            <p class="text-muted mb-0">
                Daftar akun administrator yang memiliki wewenang penuh untuk memantau proposal, mengelola data mahasiswa, dosen, serta hak akses sistem.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.administrators.create') }}" class="btn btn-danger shadow-sm">
                <i class="bi bi-shield-plus me-1"></i> Add Admin
            </a>
        </div>
    </div>
</div>

<!-- Toolbar Pencarian & Statistik -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.administrators.index') }}" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Cari berdasarkan NIP/ID Admin, Nama Lengkap, Email, atau Unit Kerja..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-danger btn-sm flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Cari Admin
                </button>
                @if(request()->filled('q'))
                    <a href="{{ route('admin.administrators.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Pencarian">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Administrator -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-people-fill text-danger me-2"></i>Daftar Akun Administrator
        </h5>
        <span class="badge bg-danger">Total: {{ $totalAdmin }} Admin Terdaftar</span>
    </div>

    <div class="card-body p-0">
        @if($administrators->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-shield-x fs-2"></i>
                <h6 class="fw-bold mt-2">Tidak Ada Data Administrator yang Sesuai</h6>
                <p class="small mb-3">Belum ada akun administrator yang terdaftar atau hasil pencarian tidak ditemukan.</p>
                <a href="{{ route('admin.administrators.create') }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-shield-plus me-1"></i> Daftarkan Admin Baru
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3" style="width: 5%;">No</th>
                            <th style="width: 15%;">NIP / ID Admin</th>
                            <th style="width: 25%;">Nama Administrator</th>
                            <th style="width: 20%;">Alamat Email</th>
                            <th style="width: 20%;">Unit Kerja / Jabatan</th>
                            <th style="width: 15%;">Terdaftar Sejak</th>
                            <th class="text-end pe-3" style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($administrators as $idx => $admin)
                            <tr>
                                <td class="ps-3 fw-semibold text-muted small">{{ $administrators->firstItem() + $idx }}</td>
                                <td>
                                    <span class="badge bg-secondary font-monospace">{{ $admin->nip_nim }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                        {{ $admin->name }}
                                        @if($admin->id === auth()->id())
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;">Akun Anda</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">Role: <span class="badge bg-danger-subtle text-danger">Administrator</span></small>
                                </td>
                                <td>
                                    <a href="mailto:{{ $admin->email }}" class="text-decoration-none text-muted small">
                                        <i class="bi bi-envelope me-1"></i>{{ $admin->email }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-dark small fw-semibold">
                                        <i class="bi bi-building me-1 text-secondary"></i>{{ $admin->kepakaran ?? 'Manajemen Sistem' }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $admin->created_at->format('d/m/Y') }} ({{ $admin->created_at->diffForHumans() }})</small>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.administrators.edit', $admin->id) }}" class="btn btn-outline-primary" title="Edit Data Admin">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        @if($admin->id !== auth()->id() && $totalAdmin > 1)
                                            <form action="{{ route('admin.administrators.destroy', $admin->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun administrator {{ $admin->name }}? Akses admin ini akan segera dicabut.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus Administrator">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-outline-secondary" disabled title="{{ $admin->id === auth()->id() ? 'Tidak dapat menghapus akun sendiri' : 'Admin terakhir tidak dapat dihapus' }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($administrators->hasPages())
                <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Menampilkan {{ $administrators->firstItem() }} sampai {{ $administrators->lastItem() }} dari {{ $administrators->total() }} administrator
                    </small>
                    {{ $administrators->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
