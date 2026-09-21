<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;

class AdminProposalController extends Controller
{
    /**
     * Menampilkan dashboard utama administrator:
     * Metrik status usulan, filter skema & status, serta tabel monitoring lengkap
     */
    public function dashboard(Request $request)
    {
        // 1. Hitung Statistik & Metrik Keseluruhan
        $stats = [
            'total'               => Proposal::count(),
            'mencari_pembimbing'  => Proposal::whereNull('dosen_id')->where('status', 'diajukan')->count(),
            'sedang_dibimbing'    => Proposal::where('status', 'sedang_dibimbing')->count(),
            'revisi'              => Proposal::where('status', 'revisi')->count(),
            'selesai'             => Proposal::where('status', 'selesai')->count(),
        ];

        // 2. Query Utama dengan Eager Loading
        $query = Proposal::with(['ketua', 'dosen', 'members', 'reviews.dosen', 'documents']);

        // Filter Berdasarkan Status
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'mencari_pembimbing') {
                $query->whereNull('dosen_id')->where('status', 'diajukan');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter Berdasarkan Skema PKM
        if ($request->filled('skema') && $request->skema !== 'all') {
            $query->where('skema_pkm', 'LIKE', '%' . $request->skema . '%');
        }

        // Pencarian Kata Kunci (Judul, Nama/NIM Mahasiswa, Nama/NIP Dosen)
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('judul_pkm', 'LIKE', "%{$search}%")
                  ->orWhereHas('ketua', function ($qk) use ($search) {
                      $qk->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('nip_nim', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('dosen', function ($qd) use ($search) {
                      $qd->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('nip_nim', 'LIKE', "%{$search}%");
                  });
            });
        }

        $proposals = $query->latest()->paginate(15)->withQueryString();

        // Daftar unik skema PKM untuk dropdown filter
        $availableSkemas = [
            'PKM-RE', 'PKM-RSH', 'PKM-K', 'PKM-PM', 'PKM-PI', 'PKM-KC', 'PKM-KI', 'PKM-VGK'
        ];

        return view('admin.dashboard', compact('stats', 'proposals', 'availableSkemas'));
    }

    /**
     * Mengambil detail lengkap satu proposal untuk inspeksi / modal audit
     */
    public function show(Proposal $proposal)
    {
        $proposal->load(['ketua', 'dosen', 'members', 'reviews.dosen', 'documents']);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success'  => true,
                'proposal' => $proposal,
            ]);
        }

        return view('admin.proposal_detail', compact('proposal'));
    }
}
