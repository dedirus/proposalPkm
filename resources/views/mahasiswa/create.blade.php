@extends('layouts.app')

@section('title', 'Form Pengajuan Proposal PKM - SIM PKM')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="fw-bold mb-0">Formulir Pengajuan Usulan PKM</h3>
                <p class="text-muted">Isi data judul, skema, unggah dokumen proposal PDF, dan input seluruh anggota tim pelaksana.</p>
            </div>
            <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <form action="{{ route('mahasiswa.proposal.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Card 1: Data Pokok Proposal -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-info-circle-fill me-1"></i> Informasi Dasar Proposal PKM
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="judul_pkm" class="form-label fw-semibold">Judul Proposal PKM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('judul_pkm') is-invalid @enderror" id="judul_pkm" name="judul_pkm" value="{{ old('judul_pkm') }}" placeholder="Contoh: Rancang Bangun Sistem Sensor Cerdas..." required>
                            @error('judul_pkm')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="skema_pkm" class="form-label fw-semibold">Skema PKM <span class="text-danger">*</span></label>
                            <select class="form-select @error('skema_pkm') is-invalid @enderror" id="skema_pkm" name="skema_pkm" required>
                                <option value="">-- Pilih Skema PKM --</option>
                                <option value="PKM-RE (Riset Eksakta)" {{ old('skema_pkm') == 'PKM-RE (Riset Eksakta)' ? 'selected' : '' }}>PKM-RE (Riset Eksakta)</option>
                                <option value="PKM-RSH (Riset Sosial Humaniora)" {{ old('skema_pkm') == 'PKM-RSH (Riset Sosial Humaniora)' ? 'selected' : '' }}>PKM-RSH (Riset Sosial Humaniora)</option>
                                <option value="PKM-K (Kewirausahaan)" {{ old('skema_pkm') == 'PKM-K (Kewirausahaan)' ? 'selected' : '' }}>PKM-K (Kewirausahaan)</option>
                                <option value="PKM-PM (Pengabdian Masyarakat)" {{ old('skema_pkm') == 'PKM-PM (Pengabdian Masyarakat)' ? 'selected' : '' }}>PKM-PM (Pengabdian Masyarakat)</option>
                                <option value="PKM-PI (Penerapan Iptek)" {{ old('skema_pkm') == 'PKM-PI (Penerapan Iptek)' ? 'selected' : '' }}>PKM-PI (Penerapan Iptek)</option>
                                <option value="PKM-KC (Karsa Cipta)" {{ old('skema_pkm') == 'PKM-KC (Karsa Cipta)' ? 'selected' : '' }}>PKM-KC (Karsa Cipta)</option>
                                <option value="PKM-KI (Karya Inovatif)" {{ old('skema_pkm') == 'PKM-KI (Karya Inovatif)' ? 'selected' : '' }}>PKM-KI (Karya Inovatif)</option>
                                <option value="PKM-VGK (Video Gagasan Konstruktif)" {{ old('skema_pkm') == 'PKM-VGK (Video Gagasan Konstruktif)' ? 'selected' : '' }}>PKM-VGK (Video Gagasan Konstruktif)</option>
                            </select>
                            @error('skema_pkm')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="proposal_file" class="form-label fw-semibold">Dokumen Proposal (Format PDF, Maks. 10MB) <span class="text-danger">*</span></label>
                            <input class="form-control @error('proposal_file') is-invalid @enderror" type="file" id="proposal_file" name="proposal_file" accept="application/pdf" required>
                            <div class="form-text">Berkas proposal wajib berekstensi .pdf dan disusun sesuai pedoman format PKM.</div>
                            @error('proposal_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Anggota Tim Dinamis (Dynamic Row) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-primary">
                            <i class="bi bi-people-fill me-1"></i> Data Anggota Tim & Jobdesk
                        </h5>
                        <small class="text-muted">Ketua tim otomatis adalah Anda ({{ auth()->user()->name }} - {{ auth()->user()->nip_nim }}). Masukkan anggota lainnya di bawah:</small>
                    </div>
                    <button type="button" id="btn-add-member" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-person-plus-fill me-1"></i> + Tambah Baris Anggota
                    </button>
                </div>
                <div class="card-body p-4">
                    <div id="members-container">
                        <!-- Baris Input Anggota Pertama (Default Index 0) -->
                        <div class="member-row card bg-light border p-3 mb-3 shadow-none" data-index="0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary member-label">Anggota Tim #1</span>
                                <button type="button" class="btn btn-danger btn-sm btn-remove-member" style="display: none;">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Nama Lengkap Anggota <span class="text-danger">*</span></label>
                                    <input type="text" name="members[0][nama_anggota]" class="form-control" placeholder="Nama mahasiswa" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">NIM Anggota <span class="text-danger">*</span></label>
                                    <input type="text" name="members[0][nim_anggota]" class="form-control" placeholder="Nomor Induk Mahasiswa" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Peran dalam Tim <span class="text-danger">*</span></label>
                                    <input type="text" name="members[0][role_anggota]" class="form-control" value="Anggota 1" placeholder="Contoh: Anggota 1 / Tim Teknis" required>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label small fw-semibold">Jobdesk & Uraian Tugas <span class="text-danger">*</span></label>
                                    <textarea name="members[0][jobdesk]" class="form-control" rows="2" placeholder="Uraikan pembagian tugas dan tanggung jawab anggota ini..." required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-secondary px-4">Batal</a>
                <button type="submit" class="btn btn-success px-4 py-2 fw-semibold">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan & Ajukan Proposal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let memberIndex = 1;
    const container = document.getElementById('members-container');
    const btnAdd = document.getElementById('btn-add-member');

    // Fungsi update tombol hapus (jangan tampilkan jika hanya tersisa 1 baris)
    function refreshRemoveButtons() {
        const rows = container.querySelectorAll('.member-row');
        rows.forEach((row, idx) => {
            const btnRemove = row.querySelector('.btn-remove-member');
            const label = row.querySelector('.member-label');
            if (label) {
                label.textContent = `Anggota Tim #${idx + 1}`;
            }
            if (rows.length === 1) {
                btnRemove.style.display = 'none';
            } else {
                btnRemove.style.display = 'inline-block';
            }
        });
    }

    // Tambah baris anggota baru secara dinamis
    btnAdd.addEventListener('click', function () {
        const newRow = document.createElement('div');
        newRow.className = 'member-row card bg-light border p-3 mb-3 shadow-none';
        newRow.setAttribute('data-index', memberIndex);

        newRow.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-secondary member-label">Anggota Tim #${memberIndex + 1}</span>
                <button type="button" class="btn btn-danger btn-sm btn-remove-member">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Nama Lengkap Anggota <span class="text-danger">*</span></label>
                    <input type="text" name="members[${memberIndex}][nama_anggota]" class="form-control" placeholder="Nama mahasiswa" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">NIM Anggota <span class="text-danger">*</span></label>
                    <input type="text" name="members[${memberIndex}][nim_anggota]" class="form-control" placeholder="Nomor Induk Mahasiswa" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Peran dalam Tim <span class="text-danger">*</span></label>
                    <input type="text" name="members[${memberIndex}][role_anggota]" class="form-control" value="Anggota ${memberIndex + 1}" placeholder="Contoh: Anggota ${memberIndex + 1}" required>
                </div>
                <div class="col-12 mt-2">
                    <label class="form-label small fw-semibold">Jobdesk & Uraian Tugas <span class="text-danger">*</span></label>
                    <textarea name="members[${memberIndex}][jobdesk]" class="form-control" rows="2" placeholder="Uraikan pembagian tugas dan tanggung jawab anggota ini..." required></textarea>
                </div>
            </div>
        `;

        container.appendChild(newRow);
        memberIndex++;
        refreshRemoveButtons();
    });

    // Event delegation untuk menghapus baris anggota tim
    container.addEventListener('click', function (e) {
        if (e.target && (e.target.classList.contains('btn-remove-member') || e.target.closest('.btn-remove-member'))) {
            const rowToRemove = e.target.closest('.member-row');
            rowToRemove.remove();
            refreshRemoveButtons();
        }
    });

    refreshRemoveButtons();
});
</script>
@endpush
