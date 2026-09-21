@extends('layouts.app')

@section('title', 'Login - SIM PKM')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow border-0">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="bg-primary text-white d-inline-flex p-3 rounded-circle mb-2">
                        <i class="bi bi-shield-lock-fill fs-2"></i>
                    </div>
                    <h4 class="fw-bold">Masuk ke Sistem PKM</h4>
                    <p class="text-muted small">Silakan masuk menggunakan akun Mahasiswa atau Dosen</p>
                </div>

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@example.com">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small" for="remember">Ingat Saya</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </button>
                </form>

                <hr class="my-4">

                <div class="bg-light p-3 rounded border">
                    <h6 class="fw-bold mb-2 small text-uppercase text-secondary">
                        <i class="bi bi-lightning-charge-fill text-warning"></i> Demo Quick-Login (1-Klik):
                    </h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('quick.login', 'mahasiswa') }}" class="btn btn-outline-primary btn-sm text-start">
                            <i class="bi bi-person-fill"></i> <strong>Mahasiswa:</strong> Budi Santoso (Ketua Tim)
                        </a>
                        <a href="{{ route('quick.login', 'dosen1') }}" class="btn btn-outline-success btn-sm text-start">
                            <i class="bi bi-person-badge-fill"></i> <strong>Dosen 1:</strong> Dr. Hendra Wijaya (AI & IoT)
                        </a>
                        <a href="{{ route('quick.login', 'dosen2') }}" class="btn btn-outline-info btn-sm text-start">
                            <i class="bi bi-person-badge"></i> <strong>Dosen 2:</strong> Prof. Siti Rahmawati (Bisnis)
                        </a>
                        <a href="{{ route('quick.login', 'admin') }}" class="btn btn-outline-dark btn-sm text-start">
                            <i class="bi bi-shield-lock-fill text-danger"></i> <strong>Admin:</strong> Administrator PKM (Monitoring)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
