@extends('layouts.app')

@section('title', 'Admin Monitoring Proposal PKM - SIM PKM')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Monitoring Usulan PKM
            </h3>
            <p class="text-muted mb-0">
                Pusat pemantauan status usulan seluruh tim mahasiswa: mencari pembimbing, proses bimbingan, perbaikan revisi, hingga usulan final.
            </p>
        </div>
        <div>
            <span class="badge bg-dark px-3 py-2 fs-6">
                <i class="bi bi-shield-check me-1"></i> Mode Administrator
            </span>
        </div>
    </div>
</div>

<!-- Widget Metrik Statistik -->
<div class="row g-3 mb-4">
    <!-- Total Usulan -->
    <div class="col-md-4 col-lg">
        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">TOTAL USULAN</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                            <i class="bi bi-files fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Mencari Pembimbing -->
    <div class="col-md-4 col-lg">
        <a href="{{ route('admin.dashboard', ['status' => 'mencari_pembimbing']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">MENCARI DOSEN</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1">{{ $stats['mencari_pembimbing'] }}</h3>
                        </div>
                        <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Sedang Dibimbing -->
    <div class="col-md-4 col-lg">
        <a href="{{ route('admin.dashboard', ['status' => 'sedang_dibimbing']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">SEDANG DIBIMBING</span>
                            <h3 class="fw-bold text-info mb-0 mt-1">{{ $stats['sedang_dibimbing'] }}</h3>
                        </div>
                        <div class="bg-info-subtle text-info p-3 rounded-circle">
                            <i class="bi bi-journal-text fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Perlu Revisi -->
    <div class="col-md-6 col-lg">
        <a href="{{ route('admin.dashboard', ['status' => 'revisi']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-danger">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">PERLU REVISI</span>
                            <h3 class="fw-bold text-danger mb-0 mt-1">{{ $stats['revisi'] }}</h3>
                        </div>
                        <div class="bg-danger-subtle text-danger p-3 rounded-circle">
                            <i class="bi bi-pencil-square fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Selesai / Disetujui -->
    <div class="col-md-6 col-lg">
        <a href="{{ route('admin.dashboard', ['status' => 'selesai']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 bg-white border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">SELESAI / FINAL</span>
                            <h3 class="fw-bold text-success mb-0 mt-1">{{ $stats['selesai'] }}</h3>
                        </div>
                        <div class="bg-success-subtle text-success p-3 rounded-circle">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Toolbar Pencarian & Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 align-items-center">
            <!-- Filter Status -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Status Usulan</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="mencari_pembimbing" {{ request('status') == 'mencari_pembimbing' ? 'selected' : '' }}>⏳ Mencari Pembimbing</option>
                    <option value="sedang_dibimbing" {{ request('status') == 'sedang_dibimbing' ? 'selected' : '' }}>📖 Sedang Dibimbing</option>
                    <option value="revisi" {{ request('status') == 'revisi' ? 'selected' : '' }}>⚠️ Perlu Revisi</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>✅ Selesai / Final</option>
                </select>
            </div>

            <!-- Filter Skema -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Skema PKM</label>
                <select name="skema" class="form-select form-select-sm">
                    <option value="all" {{ request('skema') == 'all' || !request('skema') ? 'selected' : '' }}>Semua Skema</option>
                    @foreach($availableSkemas as $skema)
                        <option value="{{ $skema }}" {{ request('skema') == $skema ? 'selected' : '' }}>{{ $skema }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Input Pencarian Kata Kunci -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Cari Usulan / Mahasiswa / Dosen</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Ketik kata kunci judul, NIM, nama..." value="{{ request('q') }}">
                </div>
            </div>

            <!-- Tombol Submit & Reset -->
            <div class="col-md-2 d-flex gap-1 align-self-end mt-2 mt-md-0">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan
                </button>
                @if(request()->hasAny(['status', 'skema', 'q']))
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Utama Monitoring Proposal -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-table text-primary me-2"></i>Data Pengajuan Usulan PKM
        </h5>
        <span class="badge bg-secondary">Total: {{ $proposals->total() }} Data</span>
    </div>

    <div class="card-body p-0">
        @if($proposals->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2"></i>
                <h6 class="fw-bold mt-2">Tidak Ada Data Usulan yang Sesuai</h6>
                <p class="small mb-0">Silakan ubah filter atau kata kunci pencarian Anda.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3" style="width: 4%;">No</th>
                            <th style="width: 32%;">Judul & Skema Usulan</th>
                            <th style="width: 20%;">Ketua Tim Mahasiswa</th>
                            <th style="width: 22%;">Dosen Pembimbing</th>
                            <th style="width: 12%;">Status Saat Ini</th>
                            <th class="text-end pe-3" style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proposals as $idx => $p)
                            <tr>
                                <td class="ps-3 fw-semibold text-muted small">{{ $proposals->firstItem() + $idx }}</td>
                                <td>
                                    <span class="badge bg-secondary mb-1" style="font-size: 0.72rem;">{{ $p->skema_pkm }}</span>
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 380px;" title="{{ $p->judul_pkm }}">
                                        {{ $p->judul_pkm }}
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-people me-1"></i> {{ $p->members->count() + 1 }} Anggota Tim &bull; 
                                        <i class="bi bi-layers me-1"></i> Versi {{ $p->documents->first()?->version ?? 1 }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $p->ketua->name }}</div>
                                    <small class="text-muted">NIM: {{ $p->ketua->nip_nim }}</small>
                                </td>
                                <td>
                                    @if(is_null($p->dosen_id))
                                        <span class="badge bg-warning-subtle text-warning border border-warning">
                                            <i class="bi bi-hourglass-split me-1"></i> Mencari Pembimbing
                                        </span>
                                    @else
                                        <div class="fw-semibold text-dark">{{ $p->dosen->name }}</div>
                                        <small class="text-muted">NIP: {{ $p->dosen->nip_nim }}</small>
                                        @if($p->dosen->kepakaran)
                                            <div style="font-size: 0.72rem;" class="text-secondary fst-italic">
                                                Bidang: {{ $p->dosen->kepakaran }}
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if(is_null($p->dosen_id) && $p->status === 'diajukan')
                                        <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-hourglass-split me-1"></i> Menunggu Dosen</span>
                                    @elseif($p->status === 'sedang_dibimbing')
                                        <span class="badge bg-primary px-2 py-1"><i class="bi bi-book me-1"></i> Sedang Dibimbing</span>
                                    @elseif($p->status === 'revisi')
                                        <span class="badge bg-danger px-2 py-1"><i class="bi bi-pencil-square me-1"></i> Perlu Revisi</span>
                                    @elseif($p->status === 'selesai')
                                        <span class="badge bg-success px-2 py-1"><i class="bi bi-check2-circle me-1"></i> Selesai / Final</span>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1">{{ $p->status }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ asset('storage/' . $p->file_path) }}" target="_blank" class="btn btn-outline-secondary" title="Buka Dokumen PDF Terkini">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-primary btn-open-detail" data-bs-toggle="modal" data-bs-target="#modalDetail-{{ $p->id }}" title="Lihat Detail Lengkap">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($proposals->hasPages())
                <div class="p-3 border-top">
                    {{ $proposals->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<!-- Modal Detail Per Usulan (Ditempatkan di luar tabel agar struktur DOM HTML valid & tombol dismiss berfungsi normal) -->
@foreach($proposals as $p)
    <div class="modal fade" id="modalDetail-{{ $p->id }}" tabindex="-1" aria-labelledby="modalDetailLabel-{{ $p->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title fw-bold" id="modalDetailLabel-{{ $p->id }}">
                        <i class="bi bi-info-circle me-1"></i> Detail Usulan: {{ $p->skema_pkm }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <h5 class="fw-bold text-dark mb-2">{{ $p->judul_pkm }}</h5>
                    
                    <!-- Status Ribbon -->
                    <div class="p-2 rounded bg-light border mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-muted">Status:</span>
                            @if($p->status === 'selesai')
                                <span class="badge bg-success">Selesai / Disetujui</span>
                            @elseif($p->status === 'revisi')
                                <span class="badge bg-danger">Perlu Revisi</span>
                            @elseif($p->status === 'sedang_dibimbing')
                                <span class="badge bg-primary">Sedang Dibimbing</span>
                            @else
                                <span class="badge bg-warning text-dark">Mencari Pembimbing</span>
                            @endif
                        </div>
                        <div>
                            <span class="small text-muted">Dosen Pembimbing:</span>
                            <strong>{{ $p->dosen?->name ?? 'Belum Ada' }}</strong>
                        </div>
                    </div>

                    <!-- Tim Pelaksana & Jobdesk -->
                    <h6 class="fw-bold border-bottom pb-1 mb-2 text-primary">
                        <i class="bi bi-people-fill me-1"></i> Susunan Tim & Pembagian Tugas
                    </h6>
                    <ul class="list-group list-group-flush mb-3 small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <div>
                                <strong>{{ $p->ketua->name }}</strong> ({{ $p->ketua->nip_nim }}) - <span class="badge bg-primary">Ketua Tim</span>
                            </div>
                        </li>
                        @foreach($p->members as $m)
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $m->nama_anggota }}</strong> ({{ $m->nim_anggota }})
                                    <span class="badge bg-secondary">{{ $m->role_anggota }}</span>
                                </div>
                                <div class="text-muted mt-1">Tugas: {{ $m->jobdesk }}</div>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Riwayat Versi Dokumen -->
                    <h6 class="fw-bold border-bottom pb-1 mb-2 text-primary">
                        <i class="bi bi-layers-fill me-1"></i> Riwayat Versi Naskah Dokumen
                    </h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered small">
                            <thead class="table-light">
                                <tr>
                                    <th>Versi</th>
                                    <th>Catatan Perubahan</th>
                                    <th>Tanggal Unggah</th>
                                    <th class="text-center">Berkas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($p->documents as $doc)
                                    <tr>
                                        <td><span class="badge bg-secondary">v{{ $doc->version }}</span></td>
                                        <td>{{ $doc->catatan_revisi ?: '-' }}</td>
                                        <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-outline-primary btn-xs">
                                                <i class="bi bi-download"></i> Buka PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada versi dokumen tambahan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Catatan Bimbingan Dosen -->
                    <h6 class="fw-bold border-bottom pb-1 mb-2 text-primary">
                        <i class="bi bi-chat-left-dots-fill me-1"></i> Catatan Evaluasi Dosen Pembimbing ({{ $p->reviews->count() }})
                    </h6>
                    <div class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
                        @forelse($p->reviews as $r)
                            <div class="border-start border-3 {{ $r->status_bagian === 'oke' ? 'border-success' : 'border-warning' }} p-2 bg-light rounded small">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">{{ $r->halaman ? 'Halaman ' . $r->halaman : 'Catatan Umum' }}</span>
                                    <span class="badge {{ $r->is_resolved ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $r->is_resolved ? 'Telah Diperbaiki' : 'Belum Diperbaiki' }}
                                    </span>
                                </div>
                                <p class="mb-1 text-dark">{{ $r->catatan_review }}</p>
                                <small class="text-muted">{{ $r->created_at->diffForHumans() }}</small>
                            </div>
                        @empty
                            <div class="text-muted small py-2">Belum ada catatan evaluasi dari pembimbing.</div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
