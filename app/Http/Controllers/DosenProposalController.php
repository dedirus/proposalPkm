<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\ProposalReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DosenProposalController extends Controller
{
    /**
     * Menampilkan daftar semua proposal mahasiswa yang belum memiliki pembimbing (dosen_id IS NULL)
     */
    public function listAvailableProposals()
    {
        $availableProposals = Proposal::with(['ketua', 'members'])
            ->whereNull('dosen_id')
            ->where('status', 'diajukan')
            ->latest()
            ->paginate(10);

        return view('dosen.available_proposals', compact('availableProposals'));
    }

    /**
     * Menampilkan daftar proposal yang sedang dibimbing oleh dosen login
     */
    public function listBimbingan()
    {
        $bimbinganList = Proposal::with(['ketua', 'members', 'reviews'])
            ->where('dosen_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('dosen.bimbingan_index', compact('bimbinganList'));
    }

    /**
     * Mengklaim proposal oleh dosen pembimbing.
     * Menggunakan Database Transaction dan lockForUpdate (Pessimistic Locking)
     * untuk mencegah "Race Condition" jika dua dosen menekan tombol klaim di detik yang sama.
     */
    public function claimProposal(Request $request, int $proposalId)
    {
        try {
            DB::transaction(function () use ($proposalId) {
                // lockForUpdate() mengunci baris (row-level lock) proposal di database
                // Query pembacaan/penguncian lain untuk baris ini akan ditahan (blocked) hingga transaksi selesai
                $proposal = Proposal::where('id', $proposalId)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Pengecekan krusial: Jika dosen_id ternyata sudah terisi oleh transaksi dosen lain yang mendahului
                if ($proposal->dosen_id !== null) {
                    throw new \DomainException('Proposal ini baru saja diklaim oleh dosen pembimbing lain.');
                }

                // Update data pembimbing dan status proposal
                $proposal->update([
                    'dosen_id' => Auth::id(),
                    'status'   => 'sedang_dibimbing',
                ]);
            });

            return redirect()->route('dosen.bimbingan.show', $proposalId)
                ->with('success', 'Selamat, proposal berhasil Anda klaim! Anda sekarang diarahkan ke ruang bimbingan.');
        } catch (\DomainException $e) {
            return redirect()->route('dosen.proposals.available')
                ->with('error', $e->getMessage());
        } catch (\Throwable $th) {
            return redirect()->route('dosen.proposals.available')
                ->with('error', 'Terjadi kesalahan sistem saat mencoba mengklaim proposal.');
        }
    }

    /**
     * Menampilkan halaman Workspace Review:
     * Sisi kiri menampilkan dokumen PDF, sisi kanan form & timeline review
     */
    public function showWorkspace(Proposal $proposal)
    {
        // Otorisasi: Hanya dosen pembimbing yang berhak membuka workspace review proposal ini
        if ($proposal->dosen_id !== Auth::id()) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Anda bukan dosen pembimbing proposal ini.');
        }

        $proposal->load(['ketua', 'members', 'reviews.dosen', 'documents']);

        return view('dosen.workspace_review', compact('proposal'));
    }

    /**
     * Mengubah status proposal (sedang_dibimbing, revisi, selesai) oleh dosen pembimbing
     */
    public function updateStatus(Request $request, Proposal $proposal)
    {
        if ($proposal->dosen_id !== Auth::id()) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Anda bukan dosen pembimbing proposal ini.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:sedang_dibimbing,revisi,selesai'],
        ], [
            'status.required' => 'Status proposal wajib dipilih.',
            'status.in'       => 'Status tidak valid.',
        ]);

        $proposal->update([
            'status' => $validated['status'],
        ]);

        $statusLabels = [
            'sedang_dibimbing' => 'Sedang Dibimbing',
            'revisi'           => 'Perlu Revisi Mahasiswa',
            'selesai'          => 'Selesai / Disetujui',
        ];

        $message = 'Status usulan PKM berhasil diubah menjadi: ' . ($statusLabels[$validated['status']] ?? $validated['status']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status'  => $proposal->status,
                'label'   => $statusLabels[$validated['status']] ?? $validated['status'],
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Menangani input catatan review dari dosen via AJAX/Fetch API (Tanpa me-reload dokumen PDF)
     */
    public function storeReviewComment(Request $request, Proposal $proposal): JsonResponse
    {
        // Validasi hak otorisasi dosen
        if ($proposal->dosen_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk mereview proposal ini.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'halaman'        => ['nullable', 'integer', 'min:1'],
            'catatan_review' => ['required', 'string'],
            'status_bagian'  => ['required', 'in:perlu_perbaikan,oke'],
        ], [
            'catatan_review.required' => 'Catatan review tidak boleh kosong.',
            'status_bagian.required'  => 'Status bagian wajib dipilih.',
            'status_bagian.in'        => 'Status bagian harus berupa "perlu_perbaikan" atau "oke".',
        ]);

        $review = $proposal->reviews()->create([
            'dosen_id'       => Auth::id(),
            'halaman'        => $validated['halaman'] ?? null,
            'catatan_review' => $validated['catatan_review'],
            'status_bagian'  => $validated['status_bagian'],
        ]);

        $review->load('dosen:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Catatan review berhasil tersimpan.',
            'data'    => [
                'id'             => $review->id,
                'halaman'        => $review->halaman,
                'catatan_review' => $review->catatan_review,
                'status_bagian'  => $review->status_bagian,
                'is_resolved'    => $review->is_resolved,
                'dosen_name'     => $review->dosen->name,
                'created_at'     => $review->created_at->diffForHumans(),
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Memperbarui catatan review dosen via AJAX (tanpa me-reload PDF)
     */
    public function updateReviewComment(Request $request, ProposalReview $review): JsonResponse
    {
        if ($review->dosen_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengedit catatan ini.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'halaman'        => ['nullable', 'integer', 'min:1'],
            'catatan_review' => ['required', 'string'],
            'status_bagian'  => ['required', 'in:perlu_perbaikan,oke'],
        ], [
            'catatan_review.required' => 'Catatan review tidak boleh kosong.',
            'status_bagian.required'  => 'Status bagian wajib dipilih.',
            'status_bagian.in'        => 'Status bagian harus berupa "perlu_perbaikan" atau "oke".',
        ]);

        $review->update([
            'halaman'        => $validated['halaman'] ?? null,
            'catatan_review' => $validated['catatan_review'],
            'status_bagian'  => $validated['status_bagian'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Catatan review berhasil diperbarui.',
            'data'    => [
                'id'             => $review->id,
                'halaman'        => $review->halaman,
                'catatan_review' => $review->catatan_review,
                'status_bagian'  => $review->status_bagian,
                'is_resolved'    => $review->is_resolved,
                'updated_at'     => $review->updated_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Menghapus catatan review dosen via AJAX (tanpa me-reload PDF)
     */
    public function destroyReviewComment(ProposalReview $review): JsonResponse
    {
        if ($review->dosen_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk menghapus catatan ini.'
            ], Response::HTTP_FORBIDDEN);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catatan review berhasil dihapus.',
            'review_id' => $review->id,
        ]);
    }
}
