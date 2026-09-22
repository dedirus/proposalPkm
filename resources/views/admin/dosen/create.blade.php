@extends('layouts.app')

@section('title', 'Add Dosen Pembimbing - Setting SIM PKM')

@section('content')
<div class="container py-2">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard Admin</a></li>
            <li class="breadcrumb-item text-muted">Setting</li>
            <li class="breadcrumb-item active" aria-current="page">Add Dosen</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-person-plus-fill text-success me-2"></i>Registrasi Dosen Pembimbing Baru
                        </h5>
                        <small class="text-muted">Daftarkan dosen yang berhak masuk ke sistem dan membimbing usulan PKM</small>
                    </div>
                    <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-people me-1"></i> Data Dosen
                    </a>
                </div>

                <div class="card-body p-4">
                    <!-- Info Alert -->
                    <div class="alert alert-success d-flex align-items-start gap-2 py-2 px-3 mb-4 small" role="alert">
                        <i class="bi bi-info-circle-fill fs-5 text-success mt-1"></i>
                        <div>
                            <strong>Informasi Akses Dosen:</strong><br>
                            Dosen yang didaftarkan melalui menu ini akan memiliki hak akses sebagai <strong>Dosen Pembimbing</strong>. Mereka dapat segera login menggunakan <strong>Email</strong> dan <strong>Password</strong> yang Anda tentukan di bawah ini untuk mengklaim usulan PKM mahasiswa dan melakukan bimbingan.
                        </div>
                    </div>

                    <form action="{{ route('admin.dosen.store') }}" method="POST">
                        @csrf

                        <!-- Nama Lengkap & Gelar -->
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-dark">
                                Nama Lengkap & Gelar Akademik <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name') }}" placeholder="Contoh: Dr. Ir. Hendra Wijaya, M.Kom." required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- NIP / NIDN -->
                        <div class="mb-3">
                            <label for="nip_nim" class="form-label small fw-semibold text-dark">
                                NIP / NIDN Dosen <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-card-heading"></i></span>
                                <input type="text" name="nip_nim" id="nip_nim" class="form-control @error('nip_nim') is-invalid @enderror" 
                                    value="{{ old('nip_nim') }}" placeholder="Contoh: 198501012010121001" required>
                                @error('nip_nim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">NIP atau NIDN harus unik dan belum pernah didaftarkan pada akun lain.</div>
                        </div>

                        <!-- Email Dosen -->
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold text-dark">
                                Alamat Email Dosen <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                    value="{{ old('email') }}" placeholder="Contoh: hendra.wijaya@univ.ac.id" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Email digunakan oleh dosen sebagai kredensial login ke sistem.</div>
                        </div>

                        <!-- Bidang Kepakaran / Keahlian -->
                        <div class="mb-3">
                            <label for="kepakaran" class="form-label small fw-semibold text-dark">
                                Bidang Kepakaran / Keahlian
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-mortarboard"></i></span>
                                <input type="text" name="kepakaran" id="kepakaran" class="form-control @error('kepakaran') is-invalid @enderror" 
                                    value="{{ old('kepakaran') }}" placeholder="Contoh: Artificial Intelligence, IoT, Bisnis Digital">
                                @error('kepakaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Bidang kepakaran membantu mahasiswa dan sistem dalam penyesuaian topik PKM yang dibimbing.</div>
                        </div>

                        <!-- Jenis Skema PKM Bimbingan -->
                        <div class="mb-3">
                            <label for="skema_pkm" class="form-label small fw-semibold text-dark">
                                Jenis Skema PKM Bimbingan <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-tags-fill"></i></span>
                                <select name="skema_pkm" id="skema_pkm" class="form-select @error('skema_pkm') is-invalid @enderror" required>
                                    @foreach($skemaOptions as $val => $label)
                                        <option value="{{ $val }}" {{ old('skema_pkm', 'All') == $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('skema_pkm')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">
                                <strong>Ketentuan:</strong> Jika dipilih <code>All</code>, dosen dapat melihat dan mengklaim seluruh usulan proposal mahasiswa dari semua skema PKM. Jika memilih skema tertentu, dosen hanya dapat melihat dan membimbing usulan yang sesuai dengan skema tersebut.
                            </div>
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
                                        placeholder="Ketik ulang password" required>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
                            </a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-person-check-fill me-1"></i> Daftarkan Dosen Pembimbing
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
