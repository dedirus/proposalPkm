@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa - SIM PKM')

@section('content')
<div class="row">
    <div class="col-12 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">Status Usulan Proposal PKM</h3>
            <p class="text-muted mb-0">Pantau perkembangan usulan dan catatan bimbingan dari dosen pembimbing.</p>
        </div>
        @if(!$proposal)
            <a href="{{ route('mahasiswa.proposal.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-file-earmark-plus"></i> Ajukan Proposal Baru
            </a>
        @endif
    </div>
</div>

@if(!$proposal)
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-file-earmark-arrow-up text-primary" style="font-size: 4rem;"></i>
            <h4 class="fw-bold mt-3">Belum Ada Proposal yang Diajukan</h4>
            <p class="text-muted">Anda belum mengajukan usulan proposal PKM. Silakan klik tombol di bawah untuk mengajukan usulan tim Anda.</p>
            <a href="{{ route('mahasiswa.proposal.create') }}" class="btn btn-primary px-4 py-2 mt-2">
                <i class="bi bi-plus-circle me-1"></i> Mulai Pengajuan Proposal
            </a>
        </div>
    </div>
@else
    @if($proposal->status === 'revisi')
        <div class="alert alert-warning border-warning shadow-sm mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Usulan Memerlukan Revisi Naskah</h6>
                <p class="mb-0 small text-dark">Dosen pembimbing telah meninjau proposal Anda dan meminta perbaikan. Silakan periksa catatan review di kolom kanan dan unggah berkas PDF naskah revisi.</p>
            </div>
            <button type="button" class="btn btn-warning fw-semibold btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUploadRevision">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Unggah Naskah Revisi
            </button>
        </div>
    @elseif($proposal->status === 'selesai')
        <div class="alert alert-success border-success shadow-sm mb-4">
            <h6 class="fw-bold mb-1"><i class="bi bi-check2-circle text-success me-2"></i> Selamat! Proposal Telah Selesai & Disetujui</h6>
            <p class="mb-0 small text-dark">Dosen pembimbing telah menyatakan proposal PKM tim Anda <strong>Selesai dan Disetujui</strong>. Naskah final ini siap diikutsertakan pada pengunggahan resmi program PKM Kemdikbudristek.</p>
        </div>
    @endif

    <div class="row g-4">
        <!-- Informasi Utama Proposal -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="badge bg-secondary px-3 py-2 fs-6">{{ $proposal->skema_pkm }}</span>
                    <div>
                        @if($proposal->status === 'diajukan')
                            <span class="badge bg-info text-dark px-3 py-2"><i class="bi bi-send me-1"></i> Diajukan</span>
                        @elseif($proposal->status === 'sedang_dibimbing')
                            <span class="badge bg-primary px-3 py-2"><i class="bi bi-book me-1"></i> Sedang Dibimbing</span>
                        @elseif($proposal->status === 'revisi')
                            <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-pencil me-1"></i> Perlu Revisi</span>
                        @elseif($proposal->status === 'selesai')
                            <span class="badge bg-success px-3 py-2"><i class="bi bi-check2-circle me-1"></i> Selesai / Disetujui</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <h4 class="fw-bold text-dark mb-3">{{ $proposal->judul_pkm }}</h4>

                    <!-- Status Pembimbing Sesuai Spesifikasi -->
                    <div class="p-3 rounded mb-4 {{ is_null($proposal->dosen_id) ? 'bg-warning-subtle border border-warning' : 'bg-success-subtle border border-success' }}">
                        <div class="d-flex align-items-center gap-2">
                            @if(is_null($proposal->dosen_id))
                                <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                                <div>
                                    <div class="fw-bold text-dark fs-6">Status Pembimbing: <span class="badge bg-warning text-dark">Mencari Pembimbing</span></div>
                                    <small class="text-muted">Proposal Anda sedang menunggu pemilihan oleh dosen pembimbing yang sesuai bidang keahlian.</small>
                                </div>
                            @else
                                <i class="bi bi-person-check-fill fs-3 text-success"></i>
                                <div>
                                    <div class="fw-bold text-success fs-6">Status Pembimbing: Sudah Ada Pembimbing: {{ $proposal->dosen->name }}</div>
                                    <small class="text-secondary">NIP: {{ $proposal->dosen->nip_nim }} &bull; Bidang Kepakaran: {{ $proposal->dosen->kepakaran ?? 'Umum' }}</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                        <a href="{{ asset('storage/' . $proposal->file_path) }}" target="_blank" class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Lihat Dokumen PDF (v{{ $proposal->documents->first()?->version ?? 1 }})
                        </a>
                        @if($proposal->status !== 'selesai' && !is_null($proposal->dosen_id))
                            <button type="button" class="btn btn-warning fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUploadRevision">
                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Unggah Naskah Revisi
                            </button>
                        @endif
                        <span class="text-muted small">Terakhir diperbarui: {{ $proposal->updated_at->format('d M Y, H:i') }} WIB</span>
                    </div>

                    <!-- Daftar Anggota Tim & Jobdesk -->
                    <h5 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="bi bi-people-fill text-primary me-1"></i> Susunan Anggota Tim & Jobdesk
                    </h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>Peran Tim</th>
                                    <th>Jobdesk / Uraian Tugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ketua Tim (User Login) -->
                                <tr class="table-primary-subtle">
                                    <td class="fw-semibold">{{ auth()->user()->nip_nim }}</td>
                                    <td>{{ auth()->user()->name }}</td>
                                    <td><span class="badge bg-primary">Ketua Tim</span></td>
                                    <td><small class="text-muted">Koordinator Utama Pengusul & Pelaksana Kegiatan PKM.</small></td>
                                </tr>
                                <!-- Anggota yang Diinputkan -->
                                @foreach($proposal->members as $member)
                                    <tr>
                                        <td class="fw-semibold">{{ $member->nim_anggota }}</td>
                                        <td>{{ $member->nama_anggota }}</td>
                                        <td><span class="badge bg-secondary">{{ $member->role_anggota }}</span></td>
                                        <td><small class="text-secondary">{{ $member->jobdesk }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Riwayat Versi Dokumen Proposal (Fitur No. 1) -->
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-layers-fill text-primary me-1"></i> Riwayat Versi Dokumen Naskah PKM
                            </h6>
                            <span class="badge bg-secondary">{{ $proposal->documents->count() }} Versi Tersimpan</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle bg-white mb-0 rounded border">
                                <thead class="table-light small">
                                    <tr>
                                        <th class="ps-3">Versi</th>
                                        <th>Catatan Perubahan</th>
                                        <th>Waktu Unggah</th>
                                        <th class="text-end pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($proposal->documents as $doc)
                                        <tr>
                                            <td class="ps-3">
                                                <span class="badge {{ $loop->first ? 'bg-success' : 'bg-secondary' }}">
                                                    v{{ $doc->version }} {{ $loop->first ? '(Terbaru)' : '' }}
                                                </span>
                                            </td>
                                            <td><small class="text-dark">{{ $doc->catatan_revisi ?: '-' }}</small></td>
                                            <td><small class="text-muted">{{ $doc->created_at->format('d M Y, H:i') }} WIB</small></td>
                                            <td class="text-end pe-3">
                                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-outline-primary btn-xs">
                                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Buka PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-2 text-muted small">
                                                <a href="{{ asset('storage/' . $proposal->file_path) }}" target="_blank">Buka Naskah Utama</a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Sisi Kanan: Catatan Review dari Dosen -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-chat-left-text-fill text-primary me-2"></i> Catatan Review Dosen
                    </h5>
                    <span class="badge bg-primary" id="totalReviewsBadge">{{ $proposal->reviews->count() }} Catatan</span>
                </div>
                <div class="card-body p-3">
                    @if($proposal->reviews->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-chat-square-dots" style="font-size: 2.5rem;"></i>
                            <p class="mt-2 mb-0 small">Belum ada catatan review yang diberikan oleh dosen pembimbing.</p>
                        </div>
                    @else
                        @php
                            $resolvedCount = $proposal->reviews->where('is_resolved', true)->count();
                            $totalCount = $proposal->reviews->count();
                            $percentage = $totalCount > 0 ? round(($resolvedCount / $totalCount) * 100) : 0;
                        @endphp
                        
                        <!-- Progress Bar Penyelesaian Perbaikan -->
                        <div class="bg-light p-2 rounded border mb-3">
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span>Progres Perbaikan:</span>
                                <span id="progressText">{{ $resolvedCount }} dari {{ $totalCount }} selesai ({{ $percentage }}%)</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" id="progressBar" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-3" id="reviewsContainer">
                            @foreach($proposal->reviews as $review)
                                <div class="card border-start border-4 {{ $review->status_bagian === 'oke' ? 'border-success' : 'border-warning' }} shadow-none p-3 review-item-card {{ $review->is_resolved ? 'bg-success-subtle' : 'bg-light' }}" id="mhs-review-{{ $review->id }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold small">
                                            <i class="bi bi-file-text"></i> {{ $review->halaman ? 'Halaman ' . $review->halaman : 'Catatan Umum' }}
                                        </span>
                                        <span class="badge {{ $review->status_bagian === 'oke' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $review->status_bagian === 'oke' ? 'Oke / Sesuai' : 'Perlu Perbaikan' }}
                                        </span>
                                    </div>

                                    <p class="mb-2 text-dark small {{ $review->is_resolved ? 'text-decoration-line-through text-muted' : '' }}" id="review-text-{{ $review->id }}">
                                        {{ $review->catatan_review }}
                                    </p>

                                    <div class="d-flex justify-content-between text-muted mb-2" style="font-size: 0.75rem;">
                                        <span>Oleh: {{ $review->dosen->name ?? 'Dosen Pembimbing' }}</span>
                                        <span>{{ $review->created_at->diffForHumans() }}</span>
                                    </div>

                                    <!-- Tombol Checklist Perbaikan -->
                                    <div>
                                        <button type="button" 
                                                class="btn btn-sm w-100 btn-toggle-resolve {{ $review->is_resolved ? 'btn-success' : 'btn-outline-success' }}" 
                                                data-id="{{ $review->id }}"
                                                data-url="{{ route('mahasiswa.reviews.toggle-resolve', $review->id) }}">
                                            @if($review->is_resolved)
                                                <i class="bi bi-check-circle-fill me-1"></i> Telah Diperbaiki (Batalkan)
                                            @else
                                                <i class="bi bi-check-circle me-1"></i> Tandai Sudah Diperbaiki
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
    </div>

    <!-- Modal Unggah Naskah Revisi (Fitur No. 1) -->
    <div class="modal fade" id="modalUploadRevision" tabindex="-1" aria-labelledby="modalUploadRevisionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark py-2">
                    <h6 class="modal-title fw-bold" id="modalUploadRevisionLabel">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Unggah Naskah Revisi Proposal
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('mahasiswa.proposal.revision', $proposal->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-3">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="bi bi-info-circle-fill me-1"></i> Berkas ini akan otomatis tercatat sebagai <strong>Versi {{ ($proposal->documents->max('version') ?? 1) + 1 }}</strong> dan status usulan akan otomatis kembali menjadi <em>Sedang Dibimbing</em>.
                        </div>

                        <div class="mb-3">
                            <label for="revision_file" class="form-label small fw-semibold">Pilih Berkas PDF Naskah Revisi <span class="text-danger">*</span></label>
                            <input type="file" name="revision_file" id="revision_file" class="form-control form-control-sm" accept="application/pdf" required>
                            <div class="form-text small">Wajib format .pdf, maksimal 10 MB.</div>
                        </div>

                        <div class="mb-2">
                            <label for="catatan_revisi" class="form-label small fw-semibold">Rangkuman / Catatan Revisi</label>
                            <textarea name="catatan_revisi" id="catatan_revisi" class="form-control form-control-sm" rows="3" placeholder="Jelaskan ringkas poin-poin yang telah Anda perbaiki (misal: Perbaikan RAB bab 4, penyesuaian tinjauan pustaka bab 2)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm fw-semibold">
                            <i class="bi bi-cloud-upload me-1"></i> Simpan & Unggah Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const container = document.getElementById('reviewsContainer');

    if (!container) return;

    container.addEventListener('click', async function (e) {
        const btn = e.target.closest('.btn-toggle-resolve');
        if (!btn) return;

        const reviewId = btn.getAttribute('data-id');
        const url = btn.getAttribute('data-url');

        btn.disabled = true;
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengupdate...';

        try {
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                const card = document.getElementById(`mhs-review-${reviewId}`);
                const text = document.getElementById(`review-text-${reviewId}`);

                if (result.is_resolved) {
                    btn.className = 'btn btn-sm w-100 btn-toggle-resolve btn-success';
                    btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Telah Diperbaiki (Batalkan)';
                    card.classList.remove('bg-light');
                    card.classList.add('bg-success-subtle');
                    text.classList.add('text-decoration-line-through', 'text-muted');
                } else {
                    btn.className = 'btn btn-sm w-100 btn-toggle-resolve btn-outline-success';
                    btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Tandai Sudah Diperbaiki';
                    card.classList.remove('bg-success-subtle');
                    card.classList.add('bg-light');
                    text.classList.remove('text-decoration-line-through', 'text-muted');
                }

                // Update progress bar
                updateProgressBar();
            } else {
                alert(result.message || 'Gagal mengubah status perbaikan.');
                btn.innerHTML = originalContent;
            }
        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan koneksi.');
            btn.innerHTML = originalContent;
        } finally {
            btn.disabled = false;
        }
    });

    function updateProgressBar() {
        const allCards = container.querySelectorAll('.review-item-card');
        const resolvedCards = container.querySelectorAll('.review-item-card.bg-success-subtle');
        const total = allCards.length;
        const resolved = resolvedCards.length;
        const percentage = total > 0 ? Math.round((resolved / total) * 100) : 0;

        const pBar = document.getElementById('progressBar');
        const pText = document.getElementById('progressText');

        if (pBar) {
            pBar.style.width = `${percentage}%`;
            pBar.setAttribute('aria-valuenow', percentage);
        }
        if (pText) {
            pText.textContent = `${resolved} dari ${total} selesai (${percentage}%)`;
        }
    }
});
</script>
@endpush
