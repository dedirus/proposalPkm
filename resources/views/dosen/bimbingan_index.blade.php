@extends('layouts.app')

@section('title', 'Daftar Bimbingan Saya - SIM PKM')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">Daftar Usulan PKM Bimbingan Saya</h3>
            <p class="text-muted mb-0">Kelola dan berikan catatan evaluasi/review untuk proposal mahasiswa yang Anda bimbing.</p>
        </div>
        <a href="{{ route('dosen.proposals.available') }}" class="btn btn-outline-primary">
            <i class="bi bi-search me-1"></i> Cari Proposal Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($bimbinganList->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-x" style="font-size: 3.5rem;"></i>
                <h5 class="fw-bold mt-2">Belum Ada Proposal yang Dibimbing</h5>
                <p class="small mb-0">Anda belum mengklaim proposal PKM manapun. Silakan buka halaman "Proposal Tersedia" untuk memilih usulan.</p>
                <a href="{{ route('dosen.proposals.available') }}" class="btn btn-primary btn-sm mt-3">
                    Lihat Usulan Tersedia
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Judul & Skema PKM</th>
                            <th>Ketua Tim</th>
                            <th>Status Usulan</th>
                            <th>Catatan Review</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bimbinganList as $index => $prop)
                            <tr>
                                <td class="ps-4 fw-semibold text-muted">{{ $bimbinganList->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-secondary mb-1">{{ $prop->skema_pkm }}</span>
                                    <div class="fw-bold text-dark fs-6">{{ $prop->judul_pkm }}</div>
                                    <small class="text-muted">Diajukan: {{ $prop->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $prop->ketua->name ?? '-' }}</div>
                                    <small class="text-muted">NIM: {{ $prop->ketua->nip_nim ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($prop->status === 'sedang_dibimbing')
                                        <span class="badge bg-primary"><i class="bi bi-book me-1"></i> Sedang Dibimbing</span>
                                    @elseif($prop->status === 'revisi')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-pencil me-1"></i> Revisi</span>
                                    @elseif($prop->status === 'selesai')
                                        <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i> Selesai</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $prop->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-chat-text-fill"></i> {{ $prop->reviews->count() }} Komentar
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('dosen.bimbingan.show', $prop->id) }}" class="btn btn-success btn-sm px-3 fw-semibold">
                                        <i class="bi bi-layout-split me-1"></i> Buka Workspace Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($bimbinganList->hasPages())
                <div class="p-3 border-top">
                    {{ $bimbinganList->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
