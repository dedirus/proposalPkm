@extends('layouts.app')

@section('title', 'Edit Dosen Pembimbing - Setting SIM PKM')

@section('content')
<div class="container py-2">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard Admin</a></li>
            <li class="breadcrumb-item text-muted">Setting</li>
            <li class="breadcrumb-item"><a href="{{ route('admin.dosen.index') }}" class="text-decoration-none">Data Dosen</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit {{ $user->name }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-pencil-square text-success me-2"></i>Edit Data Dosen Pembimbing
                        </h5>
                        <small class="text-muted">Perbarui identitas dosen, bidang kepakaran, atau atur ulang password</small>
                    </div>
                    <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.dosen.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Nama Lengkap & Gelar -->
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-dark">
                                Nama Lengkap & Gelar Akademik <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name', $user->name) }}" required autofocus>
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
                                    value="{{ old('nip_nim', $user->nip_nim) }}" required>
                                @error('nip_nim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Email Dosen -->
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold text-dark">
                                Alamat Email Dosen <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Bidang Kepakaran -->
                        <div class="mb-3">
                            <label for="kepakaran" class="form-label small fw-semibold text-dark">
                                Bidang Kepakaran / Keahlian
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-mortarboard"></i></span>
                                <input type="text" name="kepakaran" id="kepakaran" class="form-control @error('kepakaran') is-invalid @enderror" 
                                    value="{{ old('kepakaran', $user->kepakaran) }}" placeholder="Contoh: Artificial Intelligence, IoT, Bisnis Digital">
                                @error('kepakaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
                                        <option value="{{ $val }}" {{ old('skema_pkm', $user->skema_pkm ?? 'All') == $val ? 'selected' : '' }}>
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

                        <!-- Reset Password Opsional -->
                        <div class="p-3 bg-light rounded border mb-4">
                            <h6 class="fw-bold text-dark mb-1">
                                <i class="bi bi-key-fill text-warning me-1"></i> Atur Ulang Password (Opsional)
                            </h6>
                            <p class="small text-muted mb-3">Biarkan bidang di bawah ini kosong jika tidak ingin mengubah password akun dosen ini.</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label small fw-semibold text-dark">Password Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                                            placeholder="Kosongkan jika tetap">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label small fw-semibold text-dark">Konfirmasi Password Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                            placeholder="Ketik ulang password baru">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
