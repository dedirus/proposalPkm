@extends('layouts.app')

@section('title', 'Add Mahasiswa - Setting SIM PKM')

@section('content')
<div class="container py-2">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard Admin</a></li>
            <li class="breadcrumb-item text-muted">Setting</li>
            <li class="breadcrumb-item active" aria-current="page">Add Mahasiswa</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-person-plus-fill text-primary me-2"></i>Registrasi Mahasiswa Baru
                        </h5>
                        <small class="text-muted">Daftarkan mahasiswa yang berhak memiliki hak akses dan masuk ke sistem</small>
                    </div>
                    <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-people me-1"></i> Data Mahasiswa
                    </a>
                </div>

                <div class="card-body p-4">
                    <!-- Info Alert -->
                    <div class="alert alert-primary d-flex align-items-start gap-2 py-2 px-3 mb-4 small" role="alert">
                        <i class="bi bi-info-circle-fill fs-5 text-primary mt-1"></i>
                        <div>
                            <strong>Informasi Akses Sistem:</strong><br>
                            Mahasiswa yang didaftarkan melalui menu ini akan langsung terdaftar dengan peran (role) <strong>Mahasiswa</strong>. Mereka dapat segera login menggunakan <strong>Email</strong> dan <strong>Password</strong> yang Anda masukkan di bawah ini untuk mengajukan proposal PKM.
                        </div>
                    </div>

                    <form action="{{ route('admin.mahasiswa.store') }}" method="POST">
                        @csrf

                        <!-- Nama Lengkap -->
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-dark">
                                Nama Lengkap Mahasiswa <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name') }}" placeholder="Contoh: Muhammad Rizky Pratama" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- NIM (Nomor Induk Mahasiswa) -->
                        <div class="mb-3">
                            <label for="nip_nim" class="form-label small fw-semibold text-dark">
                                NIM (Nomor Induk Mahasiswa) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-card-heading"></i></span>
                                <input type="text" name="nip_nim" id="nip_nim" class="form-control @error('nip_nim') is-invalid @enderror" 
                                    value="{{ old('nip_nim') }}" placeholder="Contoh: 2108101020" required>
                                @error('nip_nim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">NIM harus unik dan belum pernah didaftarkan pada pengguna lain.</div>
                        </div>

                        <!-- Email Mahasiswa -->
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold text-dark">
                                Alamat Email Mahasiswa <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                    value="{{ old('email') }}" placeholder="Contoh: rizky@student.ac.id" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Email digunakan oleh mahasiswa sebagai identitas saat login ke sistem.</div>
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
                            <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-person-check-fill me-1"></i> Daftarkan Mahasiswa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
