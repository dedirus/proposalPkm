<?php

use App\Http\Controllers\AdminDosenController;
use App\Http\Controllers\AdminMahasiswaController;
use App\Http\Controllers\AdminProposalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DosenProposalController;
use App\Http\Controllers\MahasiswaProposalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Manajemen & Review Proposal PKM
|--------------------------------------------------------------------------
*/

// Redirect root ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick.login');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ==========================================
// AREA TERAUTENTIKASI
// ==========================================
Route::middleware(['auth'])->group(function () {

    // ==========================================
    // AREA MAHASISWA (KETUA TIM)
    // ==========================================
    Route::middleware(['role:mahasiswa'])
        ->prefix('mahasiswa')
        ->name('mahasiswa.')
        ->group(function () {
            Route::get('/dashboard', [MahasiswaProposalController::class, 'dashboardMahasiswa'])->name('dashboard');
            Route::get('/proposal/create', [MahasiswaProposalController::class, 'createProposal'])->name('proposal.create');
            Route::post('/proposal/store', [MahasiswaProposalController::class, 'storeProposal'])->name('proposal.store');
            
            // Unggah berkas revisi proposal mahasiswa
            Route::post('/proposal/{proposal}/revision', [MahasiswaProposalController::class, 'uploadRevision'])->name('proposal.revision');

            // Endpoint AJAX toggle status perbaikan review oleh mahasiswa
            Route::patch('/reviews/{review}/toggle-resolve', [MahasiswaProposalController::class, 'toggleResolveReview'])->name('reviews.toggle-resolve');
        });

    // ==========================================
    // AREA DOSEN PEMBIMBING
    // ==========================================
    Route::middleware(['role:dosen'])
        ->prefix('dosen')
        ->name('dosen.')
        ->group(function () {
            // Daftar proposal yang mencari pembimbing
            Route::get('/proposals/available', [DosenProposalController::class, 'listAvailableProposals'])->name('proposals.available');
            
            // Aksi claim proposal (Race condition safe)
            Route::post('/proposals/{proposal}/claim', [DosenProposalController::class, 'claimProposal'])->name('proposals.claim');

            // Daftar proposal yang sedang dibimbing oleh dosen login
            Route::get('/bimbingan', [DosenProposalController::class, 'listBimbingan'])->name('bimbingan.index');

            // Workspace review split view (PDF kiri, Form kanan)
            Route::get('/bimbingan/{proposal}', [DosenProposalController::class, 'showWorkspace'])->name('bimbingan.show');

            // Kontrol status proposal (sedang_dibimbing, revisi, selesai)
            Route::patch('/bimbingan/{proposal}/status', [DosenProposalController::class, 'updateStatus'])->name('bimbingan.status.update');
            
            // Endpoint AJAX untuk catatan review (tambah, edit, hapus tanpa reload PDF)
            Route::post('/bimbingan/{proposal}/review', [DosenProposalController::class, 'storeReviewComment'])->name('bimbingan.review.store');
            Route::put('/reviews/{review}', [DosenProposalController::class, 'updateReviewComment'])->name('reviews.update');
            Route::delete('/reviews/{review}', [DosenProposalController::class, 'destroyReviewComment'])->name('reviews.destroy');
        });

    // ==========================================
    // AREA ADMINISTRATOR KAMPUS (MONITORING PKM)
    // ==========================================
    Route::middleware(['role:admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            // Monitoring Usulan PKM
            Route::get('/dashboard', [AdminProposalController::class, 'dashboard'])->name('dashboard');
            Route::get('/proposals/{proposal}', [AdminProposalController::class, 'show'])->name('proposals.show');

            // Manajemen & Registrasi Mahasiswa (Setting -> Add Mahasiswa)
            Route::get('/mahasiswa', [AdminMahasiswaController::class, 'index'])->name('mahasiswa.index');
            Route::get('/mahasiswa/create', [AdminMahasiswaController::class, 'create'])->name('mahasiswa.create');
            Route::post('/mahasiswa', [AdminMahasiswaController::class, 'store'])->name('mahasiswa.store');
            Route::get('/mahasiswa/{user}/edit', [AdminMahasiswaController::class, 'edit'])->name('mahasiswa.edit');
            Route::put('/mahasiswa/{user}', [AdminMahasiswaController::class, 'update'])->name('mahasiswa.update');
            Route::delete('/mahasiswa/{user}', [AdminMahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');

            // Manajemen & Registrasi Dosen Pembimbing (Setting -> Add Dosen)
            Route::get('/dosen', [AdminDosenController::class, 'index'])->name('dosen.index');
            Route::get('/dosen/create', [AdminDosenController::class, 'create'])->name('dosen.create');
            Route::post('/dosen', [AdminDosenController::class, 'store'])->name('dosen.store');
            Route::get('/dosen/{user}/edit', [AdminDosenController::class, 'edit'])->name('dosen.edit');
            Route::put('/dosen/{user}', [AdminDosenController::class, 'update'])->name('dosen.update');
            Route::delete('/dosen/{user}', [AdminDosenController::class, 'destroy'])->name('dosen.destroy');
        });
});
