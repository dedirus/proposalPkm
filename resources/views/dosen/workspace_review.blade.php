@extends('layouts.app')

@section('title', 'Workspace Review - ' . $proposal->judul_pkm)

@section('main-class', 'p-0')
@section('container-class', 'container-fluid px-3')

@push('styles')
<style>
    .workspace-wrapper {
        height: calc(100vh - 120px);
        min-height: 580px;
    }
    .pdf-frame {
        width: 100%;
        height: 100%;
        border-radius: 8px;
    }
    .review-sidebar {
        height: 100%;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }
    .review-timeline {
        overflow-y: auto;
        flex-grow: 1;
        max-height: calc(100vh - 440px);
    }
    .btn-xs {
        padding: 0.15rem 0.4rem;
        font-size: 0.75rem;
        border-radius: 0.25rem;
    }
</style>
@endpush

@section('content')
<!-- Header Singkat -->
<div class="py-2 border-bottom d-flex justify-content-between align-items-center bg-white px-2 rounded mb-2 shadow-sm">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('dosen.bimbingan.index') }}" class="btn btn-outline-secondary btn-sm" title="Kembali ke Daftar Bimbingan">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0 text-truncate" style="max-width: 650px;" title="{{ $proposal->judul_pkm }}">
                {{ $proposal->judul_pkm }}
            </h5>
            <small class="text-muted">
                <span class="badge bg-secondary">{{ $proposal->skema_pkm }}</span> &bull; 
                Ketua Tim: <strong>{{ $proposal->ketua->name }}</strong> ({{ $proposal->ketua->nip_nim }})
            </small>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- Kontrol Status Usulan oleh Dosen (Fitur No. 2) -->
        <div class="d-flex align-items-center gap-1">
            <span class="small fw-semibold text-secondary d-none d-md-inline">Status:</span>
            <select id="selectProposalStatus" class="form-select form-select-sm fw-semibold" style="width: auto; min-width: 190px;">
                <option value="sedang_dibimbing" {{ $proposal->status === 'sedang_dibimbing' ? 'selected' : '' }}>📖 Sedang Dibimbing</option>
                <option value="revisi" {{ $proposal->status === 'revisi' ? 'selected' : '' }}>⚠️ Perlu Revisi Mahasiswa</option>
                <option value="selesai" {{ $proposal->status === 'selesai' ? 'selected' : '' }}>✅ Selesai / Disetujui</option>
            </select>
        </div>

        <a href="{{ asset('storage/' . ($proposal->documents->first()?->file_path ?? $proposal->file_path)) }}" id="btnOpenNewTab" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-box-arrow-up-right"></i> Buka PDF Tab Baru
        </a>
    </div>
</div>

<!-- Layout Split Screen: Kiri PDF, Kanan Review -->
<div class="row g-2 workspace-wrapper">
    <!-- SISI KIRI: PDF Viewer (60% layar) -->
    <div class="col-lg-7 col-xl-7 h-100">
        <div class="card h-100 border-0 shadow-sm overflow-hidden d-flex flex-column">
            <!-- Header Pemilih Versi Dokumen (Fitur No. 1) -->
            <div class="card-header bg-white py-1 px-3 d-flex justify-content-between align-items-center border-bottom">
                <span class="small fw-bold text-muted">
                    <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Penampil Dokumen PDF
                </span>
                <div class="d-flex align-items-center gap-2">
                    <label for="selectPdfVersion" class="small text-secondary mb-0 fw-semibold">Pilih Versi:</label>
                    <select id="selectPdfVersion" class="form-select form-select-sm py-0" style="width: auto; font-size: 0.82rem;">
                        @forelse($proposal->documents as $doc)
                            <option value="{{ asset('storage/' . $doc->file_path) }}" {{ $loop->first ? 'selected' : '' }}>
                                Versi {{ $doc->version }} {{ $loop->first ? '(Terbaru)' : '' }} - {{ $doc->created_at->format('d M, H:i') }}
                            </option>
                        @empty
                            <option value="{{ asset('storage/' . $proposal->file_path) }}">Versi 1 (Naskah Awal)</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <!-- Frame PDF -->
            <div class="flex-grow-1 position-relative">
                <iframe 
                    id="mainPdfIframe"
                    src="{{ asset('storage/' . ($proposal->documents->first()?->file_path ?? $proposal->file_path)) }}#toolbar=1&navpanes=0" 
                    class="pdf-frame border-0 w-100 h-100 position-absolute top-0 start-0" 
                    title="Dokumen Proposal PDF">
                    Browser Anda tidak mendukung iframe PDF. Silakan gunakan tombol buka tab baru.
                </iframe>
            </div>
        </div>
    </div>

    <!-- SISI KANAN: Form Review & Riwayat Evaluasi (40% layar) -->
    <div class="col-lg-5 col-xl-5 h-100">
        <div class="card h-100 border-0 shadow-sm review-sidebar p-3">
            
            <!-- Accordion Susunan Tim & Jobdesk -->
            <div class="accordion mb-3 shadow-none" id="teamAccordion">
                <div class="accordion-item border rounded">
                    <h2 class="accordion-header" id="headingTeam">
                        <button class="accordion-button collapsed py-2 px-3 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTeam">
                            <i class="bi bi-people-fill me-2 text-primary"></i> Tim Pengusul & Jobdesk ({{ $proposal->members->count() + 1 }} Orang)
                        </button>
                    </h2>
                    <div id="collapseTeam" class="accordion-collapse collapse" data-bs-parent="#teamAccordion">
                        <div class="accordion-body p-2 small">
                            <div class="border-bottom pb-1 mb-1">
                                <strong>{{ $proposal->ketua->name }}</strong> ({{ $proposal->ketua->nip_nim }}) - <span class="badge bg-primary">Ketua</span>
                            </div>
                            @foreach($proposal->members as $mem)
                                <div class="border-bottom pb-1 mb-1">
                                    <strong>{{ $mem->nama_anggota }}</strong> ({{ $mem->nim_anggota }}) - <span class="badge bg-secondary">{{ $mem->role_anggota }}</span>
                                    <div class="text-muted">{{ $mem->jobdesk }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Input Review Dosen (AJAX) -->
            <div class="card border bg-light p-3 mb-3 shadow-none">
                <h6 class="fw-bold mb-2 text-primary">
                    <i class="bi bi-pencil-square me-1"></i> Tambahkan Catatan Review
                </h6>

                <div id="alertFeedback" class="alert d-none py-2 px-3 small" role="alert"></div>

                <form id="formReviewComment">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label for="halaman" class="form-label small fw-semibold mb-1">Nomor Halaman (Opsional)</label>
                            <input type="number" class="form-control form-control-sm" id="halaman" placeholder="Contoh: 3" min="1">
                        </div>
                        <div class="col-6">
                            <label for="status_bagian" class="form-label small fw-semibold mb-1">Status Evaluasi <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="status_bagian" required>
                                <option value="perlu_perbaikan">Perlu Perbaikan</option>
                                <option value="oke">Oke / Sesuai</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="catatan_review" class="form-label small fw-semibold mb-1">Catatan Evaluasi / Masukan Perbaikan <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="catatan_review" rows="3" placeholder="Tuliskan detail catatan bimbingan untuk mahasiswa..." required></textarea>
                    </div>

                    <button type="submit" id="btnSubmitReview" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <i class="bi bi-send-fill me-1"></i> Simpan Catatan (Real-time)
                    </button>
                </form>
            </div>

            <!-- Riwayat Catatan Review -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-secondary">
                    <i class="bi bi-clock-history me-1"></i> Riwayat Review:
                </h6>
                <span class="badge bg-primary rounded-pill" id="reviewCountBadge">{{ $proposal->reviews->count() }}</span>
            </div>

            <div class="review-timeline pe-1" id="timelineContainer">
                @forelse($proposal->reviews as $rev)
                    <div class="card mb-2 p-2 shadow-none border-start border-4 {{ $rev->status_bagian === 'oke' ? 'border-success' : 'border-warning' }} bg-white review-card"
                         id="review-card-{{ $rev->id }}"
                         data-id="{{ $rev->id }}"
                         data-halaman="{{ $rev->halaman }}"
                         data-status="{{ $rev->status_bagian }}"
                         data-catatan="{{ $rev->catatan_review }}">
                        
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                <span class="fw-bold small review-page-label">
                                    <i class="bi bi-file-earmark-text"></i> {{ $rev->halaman ? 'Halaman ' . $rev->halaman : 'Catatan Umum' }}
                                </span>
                                <span class="badge {{ $rev->status_bagian === 'oke' ? 'bg-success' : 'bg-warning text-dark' }} small review-status-badge">
                                    {{ $rev->status_bagian === 'oke' ? 'Oke' : 'Perlu Perbaikan' }}
                                </span>
                                <span class="review-resolved-badge">
                                    @if($rev->is_resolved)
                                        <span class="badge bg-success-subtle text-success border border-success small">
                                            <i class="bi bi-check-circle-fill"></i> Selesai Diperbaiki
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary small">
                                            <i class="bi bi-hourglass"></i> Belum Diperbaiki
                                        </span>
                                    @endif
                                </span>
                            </div>

                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-outline-primary btn-xs btn-edit-review" title="Edit Catatan">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-xs btn-delete-review" title="Hapus Catatan">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <p class="mb-1 text-dark small review-text" style="white-space: pre-line;">{{ $rev->catatan_review }}</p>
                        
                        <div class="text-muted d-flex justify-content-between" style="font-size: 0.72rem;">
                            <span>{{ $rev->created_at->diffForHumans() }} &bull; Oleh: {{ $rev->dosen->name }}</span>
                            <span class="text-secondary fst-italic review-updated-label">{{ $rev->updated_at > $rev->created_at ? '(Diedit)' : '' }}</span>
                        </div>
                    </div>
                @empty
                    <div id="noReviewsPlaceholder" class="text-center py-4 text-muted small">
                        <i class="bi bi-chat-square-dots fs-3"></i>
                        <div class="mt-1">Belum ada catatan review. Gunakan formulir di atas untuk memberi masukan tanpa reload PDF.</div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</div>

<!-- Modal Edit Review Dosen -->
<div class="modal fade" id="modalEditReview" tabindex="-1" aria-labelledby="modalEditReviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold" id="modalEditReviewLabel">
                    <i class="bi bi-pencil-square me-1"></i> Edit Catatan Review
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditReview">
                <div class="modal-body p-3">
                    <input type="hidden" id="edit_review_id">
                    
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label for="edit_halaman" class="form-label small fw-semibold">Nomor Halaman</label>
                            <input type="number" class="form-control form-control-sm" id="edit_halaman" placeholder="Opsional" min="1">
                        </div>
                        <div class="col-6">
                            <label for="edit_status_bagian" class="form-label small fw-semibold">Status Evaluasi <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="edit_status_bagian" required>
                                <option value="perlu_perbaikan">Perlu Perbaikan</option>
                                <option value="oke">Oke / Sesuai</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="edit_catatan_review" class="form-label small fw-semibold">Catatan Evaluasi <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="edit_catatan_review" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSaveEditReview" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formReviewComment');
    const btnSubmit = document.getElementById('btnSubmitReview');
    const timeline = document.getElementById('timelineContainer');
    const alertBox = document.getElementById('alertFeedback');
    const countBadge = document.getElementById('reviewCountBadge');
    const placeholder = document.getElementById('noReviewsPlaceholder');

    const modalEditEl = document.getElementById('modalEditReview');
    const bsModalEdit = new bootstrap.Modal(modalEditEl);
    const formEdit = document.getElementById('formEditReview');
    const btnSaveEdit = document.getElementById('btnSaveEditReview');

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 1. Submit Review Baru (POST)
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        alertBox.className = 'alert d-none';

        const payload = {
            halaman: document.getElementById('halaman').value || null,
            status_bagian: document.getElementById('status_bagian').value,
            catatan_review: document.getElementById('catatan_review').value,
        };

        try {
            const response = await fetch("{{ route('dosen.bimbingan.review.store', $proposal->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alertBox.className = 'alert alert-success py-2 px-3 small d-block';
                alertBox.textContent = result.message;

                if (placeholder) placeholder.style.display = 'none';

                // Tambahkan elemen card baru ke DOM
                const newCard = createReviewCardElement(result.data);
                timeline.prepend(newCard);

                // Update counter
                let currentCount = parseInt(countBadge.textContent) || 0;
                countBadge.textContent = currentCount + 1;

                // Reset form
                document.getElementById('catatan_review').value = '';
                document.getElementById('halaman').value = '';

                setTimeout(() => { alertBox.className = 'alert d-none'; }, 4000);
            } else {
                let errorMsg = result.message || 'Gagal menyimpan catatan review.';
                if (result.errors) {
                    errorMsg = Object.values(result.errors).flat().join(' ');
                }
                alertBox.className = 'alert alert-danger py-2 px-3 small d-block';
                alertBox.textContent = errorMsg;
            }
        } catch (error) {
            console.error(error);
            alertBox.className = 'alert alert-danger py-2 px-3 small d-block';
            alertBox.textContent = 'Terjadi kesalahan koneksi saat menyimpan catatan.';
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-send-fill me-1"></i> Simpan Catatan (Real-time)';
        }
    });

    // 2. Event Delegation untuk Tombol Edit & Hapus
    timeline.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-edit-review');
        const btnDelete = e.target.closest('.btn-delete-review');

        if (btnEdit) {
            const card = btnEdit.closest('.review-card');
            const reviewId = card.getAttribute('data-id');
            const halaman = card.getAttribute('data-halaman');
            const status = card.getAttribute('data-status');
            const catatan = card.getAttribute('data-catatan');

            document.getElementById('edit_review_id').value = reviewId;
            document.getElementById('edit_halaman').value = halaman && halaman !== 'null' ? halaman : '';
            document.getElementById('edit_status_bagian').value = status;
            document.getElementById('edit_catatan_review').value = catatan;

            bsModalEdit.show();
        }

        if (btnDelete) {
            const card = btnDelete.closest('.review-card');
            const reviewId = card.getAttribute('data-id');

            if (confirm('Apakah Anda yakin ingin menghapus catatan review ini?')) {
                deleteReviewComment(reviewId, card);
            }
        }
    });

    // 3. Simpan Perubahan Edit (PUT)
    formEdit.addEventListener('submit', async function (e) {
        e.preventDefault();

        const reviewId = document.getElementById('edit_review_id').value;
        const payload = {
            halaman: document.getElementById('edit_halaman').value || null,
            status_bagian: document.getElementById('edit_status_bagian').value,
            catatan_review: document.getElementById('edit_catatan_review').value,
        };

        btnSaveEdit.disabled = true;
        btnSaveEdit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        try {
            const response = await fetch(`/dosen/reviews/${reviewId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                bsModalEdit.hide();

                // Update card DOM
                const card = document.getElementById(`review-card-${reviewId}`);
                if (card) {
                    card.setAttribute('data-halaman', result.data.halaman || '');
                    card.setAttribute('data-status', result.data.status_bagian);
                    card.setAttribute('data-catatan', result.data.catatan_review);

                    const isOke = result.data.status_bagian === 'oke';
                    card.className = `card mb-2 p-2 shadow-none border-start border-4 ${isOke ? 'border-success' : 'border-warning'} bg-white review-card`;
                    
                    card.querySelector('.review-page-label').innerHTML = `<i class="bi bi-file-earmark-text"></i> ${result.data.halaman ? 'Halaman ' + result.data.halaman : 'Catatan Umum'}`;
                    
                    const badge = card.querySelector('.review-status-badge');
                    badge.className = `badge ${isOke ? 'bg-success' : 'bg-warning text-dark'} small review-status-badge`;
                    badge.textContent = isOke ? 'Oke' : 'Perlu Perbaikan';

                    card.querySelector('.review-text').textContent = result.data.catatan_review;
                    
                    const updatedLabel = card.querySelector('.review-updated-label');
                    if (updatedLabel) updatedLabel.textContent = '(Diedit baru saja)';
                }
            } else {
                alert(result.message || 'Gagal memperbarui catatan review.');
            }
        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan jaringan saat menyimpan perubahan.');
        } finally {
            btnSaveEdit.disabled = false;
            btnSaveEdit.innerHTML = '<i class="bi bi-check-lg me-1"></i> Simpan Perubahan';
        }
    });

    // 4. Fungsi Hapus Catatan Review (DELETE)
    async function deleteReviewComment(reviewId, cardElement) {
        try {
            const response = await fetch(`/dosen/reviews/${reviewId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                cardElement.style.transition = 'all 0.3s ease';
                cardElement.style.opacity = '0';
                cardElement.style.transform = 'translateX(20px)';

                setTimeout(() => {
                    cardElement.remove();

                    let currentCount = parseInt(countBadge.textContent) || 1;
                    countBadge.textContent = Math.max(0, currentCount - 1);

                    if (timeline.querySelectorAll('.review-card').length === 0 && placeholder) {
                        placeholder.style.display = 'block';
                    }
                }, 300);
            } else {
                alert(result.message || 'Gagal menghapus catatan review.');
            }
        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan koneksi saat menghapus catatan.');
        }
    }

    // 5. Helper Generator Card Elemen
    function createReviewCardElement(data) {
        const card = document.createElement('div');
        const isOke = data.status_bagian === 'oke';
        const borderColor = isOke ? 'border-success' : 'border-warning';
        const badgeColor = isOke ? 'bg-success' : 'bg-warning text-dark';
        const badgeText = isOke ? 'Oke' : 'Perlu Perbaikan';
        const halText = data.halaman ? `Halaman ${data.halaman}` : 'Catatan Umum';

        card.className = `card mb-2 p-2 shadow-none border-start border-4 ${borderColor} bg-white review-card`;
        card.id = `review-card-${data.id}`;
        card.setAttribute('data-id', data.id);
        card.setAttribute('data-halaman', data.halaman || '');
        card.setAttribute('data-status', data.status_bagian);
        card.setAttribute('data-catatan', data.catatan_review);

        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="fw-bold small review-page-label">
                        <i class="bi bi-file-earmark-text"></i> ${halText}
                    </span>
                    <span class="badge ${badgeColor} small review-status-badge">${badgeText}</span>
                    <span class="review-resolved-badge">
                        <span class="badge bg-secondary-subtle text-secondary small">
                            <i class="bi bi-hourglass"></i> Belum Diperbaiki
                        </span>
                    </span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-primary btn-xs btn-edit-review" title="Edit Catatan">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-xs btn-delete-review" title="Hapus Catatan">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <p class="mb-1 text-dark small review-text" style="white-space: pre-line;">${escapeHtml(data.catatan_review)}</p>
            <div class="text-muted d-flex justify-content-between" style="font-size: 0.72rem;">
                <span>Baru saja &bull; Oleh: ${escapeHtml(data.dosen_name)}</span>
                <span class="text-secondary fst-italic review-updated-label"></span>
            </div>
        `;
        return card;
    }

    // 6. Ganti Versi Dokumen PDF di Viewer secara Instan (Fitur No. 1)
    const selectPdf = document.getElementById('selectPdfVersion');
    const mainIframe = document.getElementById('mainPdfIframe');
    const btnNewTab = document.getElementById('btnOpenNewTab');

    if (selectPdf && mainIframe) {
        selectPdf.addEventListener('change', function () {
            const newUrl = this.value;
            mainIframe.src = newUrl + '#toolbar=1&navpanes=0';
            if (btnNewTab) {
                btnNewTab.href = newUrl;
            }
        });
    }

    // 7. Kontrol Ubah Status Proposal Real-time oleh Dosen (Fitur No. 2)
    const selectStatus = document.getElementById('selectProposalStatus');
    if (selectStatus) {
        selectStatus.addEventListener('change', async function () {
            const newStatus = this.value;
            const originalValue = this.getAttribute('data-current') || newStatus;

            try {
                const response = await fetch("{{ route('dosen.bimbingan.status.update', $proposal->id) }}", {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    selectStatus.setAttribute('data-current', newStatus);
                    alertBox.className = 'alert alert-info py-2 px-3 small d-block';
                    alertBox.innerHTML = `<i class="bi bi-info-circle-fill me-1"></i> ${result.message}`;
                    setTimeout(() => { alertBox.className = 'alert d-none'; }, 4000);
                } else {
                    alert(result.message || 'Gagal mengubah status proposal.');
                    selectStatus.value = originalValue;
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan koneksi saat mengubah status proposal.');
                selectStatus.value = originalValue;
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function (m) { return map[m]; });
    }
});
</script>
@endpush
