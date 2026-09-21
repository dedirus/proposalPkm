@extends('layouts.app')

@section('title', 'Data Mahasiswa Terdaftar - Setting SIM PKM')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>Data Mahasiswa Berhak Masuk Sistem
            </h3>
            <p class="text-muted mb-0">
                Daftar mahasiswa yang telah diregistrasi oleh Admin dan memiliki hak akses untuk login ke dalam SIM-PKM.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.mahasiswa.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> Add Mahasiswa
            </a>
        </div>
    </div>
</div>

<!-- Toolbar Pencarian & Statistik -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.mahasiswa.index') }}" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Cari berdasarkan NIM, Nama Mahasiswa, atau Alamat Email..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Cari Mahasiswa
                </button>
                @if(request()->filled('q'))
                    <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Pencarian">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Mahasiswa -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-person-lines-fill text-primary me-2"></i>Daftar Akun Mahasiswa
        </h5>
        <span class="badge bg-primary">Total: {{ $totalMahasiswa }} Mahasiswa Terdaftar</span>
    </div>

    <div class="card-body p-0">
        @if($mahasiswas->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-person-x fs-2"></i>
                <h6 class="fw-bold mt-2">Tidak Ada Data Mahasiswa yang Sesuai</h6>
                <p class="small mb-3">Belum ada mahasiswa yang terdaftar atau hasil pencarian tidak ditemukan.</p>
                <a href="{{ route('admin.mahasiswa.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i> Daftarkan Mahasiswa Pertama
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3" style="width: 5%;">No</th>
                            <th style="width: 15%;">NIM</th>
                            <th style="width: 30%;">Nama Lengkap</th>
                            <th style="width: 25%;">Alamat Email</th>
                            <th style="width: 13%;">Usulan PKM</th>
                            <th class="text-end pe-3" style="width: 12%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mahasiswas as $idx => $m)
                            <tr>
                                <td class="ps-3 fw-semibold text-muted small">{{ $mahasiswas->firstItem() + $idx }}</td>
                                <td>
                                    <span class="badge bg-secondary font-monospace">{{ $m->nip_nim }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $m->name }}</div>
                                    <small class="text-muted">Terdaftar: {{ $m->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <a href="mailto:{{ $m->email }}" class="text-decoration-none text-muted small">
                                        <i class="bi bi-envelope me-1"></i>{{ $m->email }}
                                    </a>
                                </td>
                                <td>
                                    @if($m->proposals_ketua_count > 0)
                                        <span class="badge bg-success-subtle text-success border border-success">
                                            <i class="bi bi-file-earmark-check me-1"></i> {{ $m->proposals_ketua_count }} Usulan Diajukan
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border">
                                            Belum Mengajukan
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.mahasiswa.edit', $m->id) }}" class="btn btn-outline-primary" title="Edit Data Mahasiswa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($m->proposals_ketua_count == 0)
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDelete-{{ $m->id }}" title="Hapus Mahasiswa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-outline-secondary disabled" title="Tidak dapat dihapus karena memiliki usulan proposal aktif">
                                                <i class="bi bi-lock-fill"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($mahasiswas->hasPages())
                <div class="p-3 border-top">
                    {{ $mahasiswas->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<!-- Modal Konfirmasi Hapus (Ditempatkan di luar tabel untuk struktur DOM yang valid) -->
@foreach($mahasiswas as $m)
    @if($m->proposals_ketua_count == 0)
        <div class="modal fade" id="modalDelete-{{ $m->id }}" tabindex="-1" aria-labelledby="modalDeleteLabel-{{ $m->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white py-2">
                        <h6 class="modal-title fw-bold" id="modalDeleteLabel-{{ $m->id }}">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Konfirmasi Hapus Akun Mahasiswa
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Batal"></button>
                    </div>
                    <form action="{{ route('admin.mahasiswa.destroy', $m->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body p-4 text-center">
                            <i class="bi bi-person-x-fill text-danger fs-1 mb-2"></i>
                            <h5 class="fw-bold text-dark">Hapus Mahasiswa Ini?</h5>
                            <p class="text-muted small mb-0">
                                Apakah Anda yakin ingin menghapus data akun <strong>{{ $m->name }}</strong> (NIM: {{ $m->nip_nim }})? Setelah dihapus, mahasiswa ini tidak lagi dapat masuk ke dalam sistem.
                            </p>
                        </div>
                        <div class="modal-footer py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus Mahasiswa</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
