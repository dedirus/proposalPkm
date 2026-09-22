@extends('layouts.app')

@section('title', 'Data Dosen Pembimbing - Setting SIM PKM')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-person-badge-fill text-success me-2"></i>Data Dosen Pembimbing PKM
            </h3>
            <p class="text-muted mb-0">
                Daftar dosen pembimbing yang memiliki wewenang untuk mereview, membimbing, dan menyetujui usulan PKM mahasiswa.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.dosen.create') }}" class="btn btn-success shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> Add Dosen
            </a>
        </div>
    </div>
</div>

<!-- Toolbar Pencarian & Statistik -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.dosen.index') }}" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Cari berdasarkan NIP/NIDN, Nama Dosen, Email, atau Bidang Kepakaran..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Cari Dosen
                </button>
                @if(request()->filled('q'))
                    <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Pencarian">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Dosen Pembimbing -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-people-fill text-success me-2"></i>Daftar Akun Dosen Pembimbing
        </h5>
        <span class="badge bg-success">Total: {{ $totalDosen }} Dosen Terdaftar</span>
    </div>

    <div class="card-body p-0">
        @if($dosens->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-person-x fs-2"></i>
                <h6 class="fw-bold mt-2">Tidak Ada Data Dosen yang Sesuai</h6>
                <p class="small mb-3">Belum ada dosen yang terdaftar atau hasil pencarian tidak ditemukan.</p>
                <a href="{{ route('admin.dosen.create') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-person-plus-fill me-1"></i> Daftarkan Dosen Pertama
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3" style="width: 5%;">No</th>
                            <th style="width: 15%;">NIP / NIDN</th>
                            <th style="width: 25%;">Nama Lengkap & Gelar</th>
                            <th style="width: 20%;">Email</th>
                            <th style="width: 20%;">Bidang Kepakaran</th>
                            <th style="width: 15%;">Usulan Dibimbing</th>
                            <th class="text-end pe-3" style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dosens as $idx => $d)
                            <tr>
                                <td class="ps-3 fw-semibold text-muted small">{{ $dosens->firstItem() + $idx }}</td>
                                <td>
                                    <span class="badge bg-secondary font-monospace">{{ $d->nip_nim }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $d->name }}</div>
                                    <small class="text-muted">Terdaftar: {{ $d->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <a href="mailto:{{ $d->email }}" class="text-decoration-none text-muted small">
                                        <i class="bi bi-envelope me-1"></i>{{ $d->email }}
                                    </a>
                                </td>
                                <td>
                                    @if($d->kepakaran)
                                        <div class="small fw-semibold text-dark">
                                            <i class="bi bi-mortarboard text-primary me-1"></i>{{ $d->kepakaran }}
                                        </div>
                                    @else
                                        <div class="text-muted small fst-italic">Kepakaran belum diisi</div>
                                    @endif
                                    <div class="mt-1">
                                        @if(empty($d->skema_pkm) || strtolower($d->skema_pkm) === 'all')
                                            <span class="badge bg-success-subtle text-success border border-success" style="font-size: 0.72rem;">
                                                <i class="bi bi-check2-all me-1"></i>Semua Skema (All)
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning" style="font-size: 0.72rem;">
                                                <i class="bi bi-tag-fill me-1"></i>{{ $d->skema_pkm }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($d->proposals_bimbingan_count > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary">
                                            <i class="bi bi-journal-bookmark-fill me-1"></i> {{ $d->proposals_bimbingan_count }} Proposal
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border">
                                            0 Proposal
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.dosen.edit', $d->id) }}" class="btn btn-outline-primary" title="Edit Data Dosen">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($d->proposals_bimbingan_count == 0)
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteDosen-{{ $d->id }}" title="Hapus Dosen">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-outline-secondary disabled" title="Tidak dapat dihapus karena sedang membimbing usulan proposal aktif">
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

            @if($dosens->hasPages())
                <div class="p-3 border-top">
                    {{ $dosens->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<!-- Modal Konfirmasi Hapus (Ditempatkan di luar tabel untuk validitas struktur HTML) -->
@foreach($dosens as $d)
    @if($d->proposals_bimbingan_count == 0)
        <div class="modal fade" id="modalDeleteDosen-{{ $d->id }}" tabindex="-1" aria-labelledby="modalDeleteDosenLabel-{{ $d->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white py-2">
                        <h6 class="modal-title fw-bold" id="modalDeleteDosenLabel-{{ $d->id }}">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Konfirmasi Hapus Akun Dosen
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Batal"></button>
                    </div>
                    <form action="{{ route('admin.dosen.destroy', $d->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body p-4 text-center">
                            <i class="bi bi-person-x-fill text-danger fs-1 mb-2"></i>
                            <h5 class="fw-bold text-dark">Hapus Akun Dosen Ini?</h5>
                            <p class="text-muted small mb-0">
                                Apakah Anda yakin ingin menghapus akun Dosen Pembimbing <strong>{{ $d->name }}</strong> (NIP: {{ $d->nip_nim }})? Setelah dihapus, akun ini tidak lagi dapat masuk ke sistem.
                            </p>
                        </div>
                        <div class="modal-footer py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus Dosen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
