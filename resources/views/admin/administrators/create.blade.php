@extends('layouts.app')

@section('title', 'Add Administrator - Setting SIM PKM')

@section('content')
<div class="container py-2">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard Admin</a></li>
            <li class="breadcrumb-item text-muted">Setting</li>
            <li class="breadcrumb-item active" aria-current="page">Add Administrator</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-shield-plus text-danger me-2"></i>Registrasi Administrator Baru
                        </h5>
                        <small class="text-muted">Daftarkan akun administrator kampus baru dengan hak akses penuh ke sistem</small>
                    </div>
                    <a href="{{ route('admin.administrators.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-shield-lock me-1"></i> Data Admin
                    </a>
                </div>

                <div class="card-body p-4">
                    <!-- Info Alert -->
                    <div class="alert alert-danger d-flex align-items-start gap-2 py-2 px-3 mb-4 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-danger mt-1"></i>
                        <div>
                            <strong>Pemberian Hak Akses Super Administrator:</strong><br>
                            Akun yang didaftarkan melalui menu ini akan memiliki wewenang penuh untuk memantau proposal seluruh skema PKM, mengelola data mahasiswa, dosen, serta mendaftarkan administrator lainnya.
                        </div>
                    </div>

                    <form action="{{ route('admin.administrators.store') }}" method="POST">
                        @csrf

                        <!-- Nama Lengkap -->
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-dark">
                                Nama Lengkap Administrator <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name') }}" placeholder="Contoh: Rian Pratama, S.Kom." required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- NIP / ID Administrator -->
                        <div class="mb-3">
                            <label for="nip_nim" class="form-label small fw-semibold text-dark">
                                NIP / ID Staf Administrator <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-card-heading"></i></span>
                                <input type="text" name="nip_nim" id="nip_nim" class="form-control @error('nip_nim') is-invalid @enderror" 
                                    value="{{ old('nip_nim') }}" placeholder="Contoh: 198501012010121005 atau ADM-002" required>
                                @error('nip_nim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">NIP atau ID unik pegawai yang digunakan sebagai pengenal akun administrator.</div>
                        </div>

                        <!-- Email Administrator -->
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold text-dark">
                                Alamat Email Administrator <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                    value="{{ old('email') }}" placeholder="Contoh: admin.kemahasiswaan@kampus.ac.id" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Email digunakan oleh administrator sebagai kredensial saat login ke sistem.</div>
                        </div>

                        <!-- Unit Kerja / Jabatan -->
                        <div class="mb-3">
                            <label for="kepakaran" class="form-label small fw-semibold text-dark">
                                Unit Kerja / Jabatan
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                <input type="text" name="kepakaran" id="kepakaran" class="form-control @error('kepakaran') is-invalid @enderror" 
                                    value="{{ old('kepakaran', 'Biro Kemahasiswaan & Penalaran') }}" placeholder="Contoh: Biro Kemahasiswaan & PKM">
                                @error('kepakaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Deskripsi bagian atau divisi tempat administrator bertugas.</div>
                        </div>

                        <hr class="my-4">

                        <!-- Password -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label small fw-semibold text-dark">
                                    Password Akses <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                        placeholder="Minimal 6 karakter" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label small fw-semibold text-dark">
                                    Konfirmasi Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                        placeholder="Ulangi password di atas" required>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.administrators.index') }}" class="btn btn-outline-secondary px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-danger px-4 fw-semibold shadow-sm">
                                <i class="bi bi-shield-check me-1"></i> Simpan Administrator
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
