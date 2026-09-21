@extends('layouts.app')

@section('title', 'Daftar Proposal Tersedia untuk Bimbingan - SIM PKM')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">Proposal Mahasiswa Tersedia (Mencari Pembimbing)</h3>
            <p class="text-muted mb-0">Pilih dan klaim usulan PKM mahasiswa yang sesuai dengan bidang kepakaran Anda: 
                <span class="badge bg-primary fs-6">{{ auth()->user()->kepakaran ?? 'Umum' }}</span>
            </p>
        </div>
        <a href="{{ route('dosen.bimbingan.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-journal-bookmark-fill me-1"></i> Bimbingan Saya
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($availableProposals->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check2-all text-success" style="font-size: 3.5rem;"></i>
                <h5 class="fw-bold mt-2">Tidak Ada Usulan yang Menunggu Pembimbing</h5>
                <p class="small mb-0">Semua usulan PKM saat ini telah memiliki dosen pembimbing atau belum ada usulan baru yang masuk.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 5%;">No</th>
                            <th style="width: 45%;">Judul & Skema PKM</th>
                            <th style="width: 25%;">Ketua Tim & Anggota</th>
                            <th style="width: 10%;">Berkas</th>
                            <th class="text-end pe-4" style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($availableProposals as $index => $prop)
                            <tr>
                                <td class="ps-4 fw-semibold text-muted">{{ $availableProposals->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-secondary mb-1">{{ $prop->skema_pkm }}</span>
                                    <div class="fw-bold text-dark fs-6">{{ $prop->judul_pkm }}</div>
                                    <small class="text-muted">Diajukan: {{ $prop->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $prop->ketua->name ?? 'Mahasiswa' }}</div>
                                    <small class="text-muted">NIM: {{ $prop->ketua->nip_nim ?? '-' }}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-people"></i> {{ $prop->members->count() }} Anggota Tim
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ asset('storage/' . $prop->file_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="Pratinjau PDF">
                                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i> PDF
                                    </a>
                                </td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('dosen.proposals.claim', $prop->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menjadi Dosen Pembimbing untuk proposal ini?');">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                                            <i class="bi bi-check-circle-fill me-1"></i> Klaim Bimbingan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($availableProposals->hasPages())
                <div class="p-3 border-top">
                    {{ $availableProposals->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
