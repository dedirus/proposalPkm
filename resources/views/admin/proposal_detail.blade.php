@extends('layouts.app')

@section('title', 'Detail Usulan PKM - ' . $proposal->judul_pkm)

@section('content')
<div class="container py-4">
    <!-- Breadcrumb & Back Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm" title="Kembali ke Dashboard Admin">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
            <span class="text-muted">/</span>
            <span class="fw-semibold text-secondary">Detail Usulan #{{ $proposal->id }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('proposals.file', $proposal) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Buka Naskah PDF Terkini
            </a>
        </div>
    </div>

    <!-- Ringkasan Usulan Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-primary px-2.5 py-1.5 fs-6">{{ $proposal->skema_pkm }}</span>
                        @if($proposal->status === 'diajukan')
                            <span class="badge bg-warning text-dark px-2.5 py-1.5"><i class="bi bi-hourglass-split me-1"></i> Menunggu Dosen Pembimbing</span>
                        @elseif($proposal->status === 'sedang_dibimbing')
                            <span class="badge bg-info text-white px-2.5 py-1.5"><i class="bi bi-book me-1"></i> Sedang Dibimbing</span>
                        @elseif($proposal->status === 'revisi')
                            <span class="badge bg-danger px-2.5 py-1.5"><i class="bi bi-pencil-square me-1"></i> Perlu Revisi Mahasiswa</span>
                        @elseif($proposal->status === 'selesai')
                            <span class="badge bg-success px-2.5 py-1.5"><i class="bi bi-check2-circle me-1"></i> Selesai / Disetujui</span>
                        @else
                            <span class="badge bg-secondary px-2.5 py-1.5">{{ $proposal->status }}</span>
                        @endif
                    </div>
                    <h3 class="fw-bold mb-2 text-dark">{{ $proposal->judul_pkm }}</h3>
                    <p class="text-muted small mb-0">Diajukan pada: {{ $proposal->created_at->translatedFormat('d F Y, H:i') }} &bull; Terakhir diperbarui: {{ $proposal->updated_at->diffForHumans() }}</p>
                </div>
            </div>

            <hr class="my-3 text-secondary opacity-25">

            <!-- Info Ketua & Dosen -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="small fw-bold text-uppercase text-muted mb-2"><i class="bi bi-person-fill text-primary me-1"></i> Ketua Tim Mahasiswa</div>
                        <div class="fw-bold fs-5 text-dark">{{ $proposal->ketua->name }}</div>
                        <div class="text-secondary small">NIM: <strong>{{ $proposal->ketua->nip_nim }}</strong></div>
                        <div class="text-secondary small">Email: {{ $proposal->ketua->email }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="small fw-bold text-uppercase text-muted mb-2"><i class="bi bi-person-badge-fill text-success me-1"></i> Dosen Pembimbing</div>
                        @if($proposal->dosen)
                            <div class="fw-bold fs-5 text-dark">{{ $proposal->dosen->name }}</div>
                            <div class="text-secondary small">NIP/NIDN: <strong>{{ $proposal->dosen->nip_nim }}</strong></div>
                            <div class="text-secondary small">Kepakaran: {{ $proposal->dosen->kepakaran ?? '-' }} &bull; Skema: {{ $proposal->dosen->skema_pkm ?? 'All' }}</div>
                        @else
                            <div class="text-warning fw-bold fs-5"><i class="bi bi-exclamation-triangle me-1"></i> Belum Memiliki Pembimbing</div>
                            <div class="text-muted small">Usulan ini masih berstatus terbuka untuk diklaim oleh dosen pembimbing di bidang {{ $proposal->skema_pkm }}.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Tab Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs card-header-tabs m-0 px-3 pt-2" id="detailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold" id="tim-tab" data-bs-toggle="tab" data-bs-target="#tim-pane" type="button" role="tab" aria-controls="tim-pane" aria-selected="true">
                        <i class="bi bi-people-fill text-primary me-1"></i> Susunan Tim Pelaksana ({{ $proposal->members->count() + 1 }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="versi-tab" data-bs-toggle="tab" data-bs-target="#versi-pane" type="button" role="tab" aria-controls="versi-pane" aria-selected="false">
                        <i class="bi bi-layers-fill text-warning me-1"></i> Riwayat Versi Dokumen (v{{ $proposal->documents->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="review-tab" data-bs-toggle="tab" data-bs-target="#review-pane" type="button" role="tab" aria-controls="review-pane" aria-selected="false">
                        <i class="bi bi-chat-left-dots-fill text-info me-1"></i> Catatan Evaluasi Review ({{ $proposal->reviews->count() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="detailTabsContent">
                <!-- Tab 1: Susunan Tim -->
                <div class="tab-pane fade show active" id="tim-pane" role="tabpanel" aria-labelledby="tim-tab" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nama Lengkap</th>
                                    <th>NIM</th>
                                    <th>Peran dalam Tim</th>
                                    <th>Tugas & Jobdesk</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold">1</td>
                                    <td>
                                        <strong>{{ $proposal->ketua->name }}</strong>
                                        <div class="small text-muted">{{ $proposal->ketua->email }}</div>
                                    </td>
                                    <td><code>{{ $proposal->ketua->nip_nim }}</code></td>
                                    <td><span class="badge bg-primary">Ketua Tim</span></td>
                                    <td>Mengkoordinasikan seluruh pelaksanaan proposal kegiatan PKM.</td>
                                </tr>
                                @forelse($proposal->members as $index => $member)
                                    <tr>
                                        <td>{{ $index + 2 }}</td>
                                        <td><strong>{{ $member->nama_anggota }}</strong></td>
                                        <td><code>{{ $member->nim_anggota }}</code></td>
                                        <td><span class="badge bg-secondary">{{ $member->role_anggota }}</span></td>
                                        <td>{{ $member->jobdesk }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Tidak ada data anggota tambahan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Riwayat Dokumen -->
                <div class="tab-pane fade" id="versi-pane" role="tabpanel" aria-labelledby="versi-tab" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Versi Naskah</th>
                                    <th>Catatan Revisi / Perubahan</th>
                                    <th>Waktu Unggah</th>
                                    <th class="text-end">Aksi Berkas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($proposal->documents as $doc)
                                    <tr>
                                        <td>
                                            <span class="badge bg-dark fs-6">Versi {{ $doc->version }}</span>
                                            @if($loop->first)
                                                <span class="badge bg-success ms-1">Terbaru</span>
                                            @endif
                                        </td>
                                        <td>{{ $doc->catatan_revisi ?: 'Naskah naskah pengajuan proposal.' }}</td>
                                        <td>{{ $doc->created_at->translatedFormat('d F Y, H:i') }} ({{ $doc->created_at->diffForHumans() }})</td>
                                        <td class="text-end">
                                            <a href="{{ route('proposals.documents.show', [$proposal, $doc]) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-file-earmark-pdf me-1"></i> Buka Naskah PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat dokumen tambahan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Catatan Evaluasi Review -->
                <div class="tab-pane fade" id="review-pane" role="tabpanel" aria-labelledby="review-tab" tabindex="0">
                    @forelse($proposal->reviews as $r)
                        <div class="card border-0 bg-light mb-3 border-start border-4 {{ $r->status_bagian === 'oke' ? 'border-success' : 'border-warning' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold fs-6">
                                            <i class="bi bi-bookmark-fill text-secondary me-1"></i>{{ $r->halaman ? 'Halaman ' . $r->halaman : 'Catatan Umum' }}
                                        </span>
                                        @if($r->status_bagian === 'oke')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Bagian Disetujui (Oke)</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Perlu Perbaikan</span>
                                        @endif
                                    </div>
                                    <div>
                                        @if($r->is_resolved)
                                            <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Telah Ditandai Diperbaiki</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-hourglass-split me-1"></i> Belum Diperbaiki</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-dark mb-2">{{ $r->catatan_review }}</p>
                                @if($r->catatan_mahasiswa)
                                    <div class="p-2.5 bg-white rounded border small mb-2">
                                        <strong class="text-primary"><i class="bi bi-reply-fill me-1"></i> Respon / Catatan Mahasiswa:</strong>
                                        <div class="text-secondary mt-1">{{ $r->catatan_mahasiswa }}</div>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>Reviewer: <strong>{{ $r->dosen->name ?? ($proposal->dosen?->name ?? 'Dosen Pembimbing') }}</strong></span>
                                    <span>{{ $r->created_at->translatedFormat('d M Y, H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-text fs-1 text-secondary opacity-50 d-block mb-2"></i>
                            <h6 class="fw-semibold">Belum Ada Catatan Evaluasi</h6>
                            <p class="small text-muted mb-0">Dosen pembimbing belum memberikan catatan review untuk usulan ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
