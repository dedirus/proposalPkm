<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MahasiswaProposalController extends Controller
{
    /**
     * Menampilkan dashboard status proposal mahasiswa saat ini
     */
    public function dashboardMahasiswa()
    {
        $proposal = Proposal::with(['dosen', 'members', 'reviews.dosen', 'documents'])
            ->where('ketua_id', Auth::id())
            ->latest()
            ->first();

        return view('mahasiswa.dashboard', compact('proposal'));
    }

    /**
     * Menampilkan formulir pengajuan proposal baru
     */
    public function createProposal()
    {
        // Opsional: cek jika mahasiswa sudah memiliki proposal aktif
        $hasActiveProposal = Proposal::where('ketua_id', Auth::id())
            ->whereIn('status', ['diajukan', 'sedang_dibimbing', 'revisi'])
            ->exists();

        return view('mahasiswa.create', compact('hasActiveProposal'));
    }

    /**
     * Menangani upload berkas PDF ke 'public/proposals' & bulk input data anggota tim
     */
    public function storeProposal(Request $request)
    {
        $validated = $request->validate([
            'judul_pkm'              => ['required', 'string', 'max:255'],
            'skema_pkm'              => ['required', 'string', 'max:100'],
            'proposal_file'          => ['required', 'file', 'mimes:pdf', 'max:10240'], // Maksimal 10MB
            'members'                => ['required', 'array', 'min:1'],
            'members.*.nama_anggota' => ['required', 'string', 'max:255'],
            'members.*.nim_anggota'  => ['required', 'string', 'max:50'],
            'members.*.role_anggota' => ['required', 'string', 'max:100'],
            'members.*.jobdesk'      => ['required', 'string'],
        ], [
            'proposal_file.mimes'    => 'Format berkas proposal harus berupa dokumen PDF.',
            'proposal_file.max'      => 'Ukuran file PDF proposal maksimal adalah 10 MB.',
            'members.required'       => 'Minimal harus ada 1 anggota tim yang didaftarkan.',
            'members.*.nama_anggota.required' => 'Nama anggota tim wajib diisi.',
            'members.*.nim_anggota.required'  => 'NIM anggota tim wajib diisi.',
            'members.*.role_anggota.required' => 'Peran anggota tim wajib diisi.',
            'members.*.jobdesk.required'      => 'Jobdesk anggota tim wajib diisi.',
        ]);

        $filePath = null;

        try {
            // Gunakan Database Transaction untuk memastikan konsistensi penyimpanan proposal & anggotanya
            $proposal = DB::transaction(function () use ($request, $validated, &$filePath) {
                // 1. Simpan dokumen PDF ke storage disk 'public' di direktori 'proposals'
                $filePath = $request->file('proposal_file')->store('proposals', 'public');

                // 2. Simpan entri data proposal
                $proposal = Proposal::create([
                    'ketua_id'  => Auth::id(),
                    'judul_pkm' => $validated['judul_pkm'],
                    'skema_pkm' => $validated['skema_pkm'],
                    'file_path' => $filePath,
                    'status'    => 'diajukan',
                ]);

                // 3. Bulk insert / Mass-insertion data anggota tim beserta detail peran dan jobdesk-nya
                // createMany otomatis mengisi foreign key 'proposal_id' dan timestamps 'created_at' & 'updated_at'
                $proposal->members()->createMany($validated['members']);

                // 4. Catat dokumen versi 1 (v1) pada riwayat versi dokumen
                $proposal->documents()->create([
                    'version'        => 1,
                    'file_path'      => $filePath,
                    'catatan_revisi' => 'Naskah awal pengajuan proposal PKM.',
                ]);

                return $proposal;
            });

            return redirect()->route('mahasiswa.dashboard')
                ->with('success', 'Proposal PKM beserta data anggota tim berhasil diajukan!');
        } catch (\Throwable $th) {
            // Rollback berkas fisik jika transaksi database mengalami kegagalan
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return back()->withInput()->with('error', 'Gagal mengajukan proposal: ' . $th->getMessage());
        }
    }

    /**
     * Menangani unggah berkas PDF revisi oleh mahasiswa dengan pencatatan nomor versi otomatis
     */
    public function uploadRevision(Request $request, Proposal $proposal)
    {
        // Validasi: Pastikan mahasiswa login adalah pemilik proposal
        if ($proposal->ketua_id !== Auth::id()) {
            abort(\Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN, 'Akses ditolak: Anda bukan ketua tim proposal ini.');
        }

        // Proposal yang sudah selesai tidak dapat direvisi lagi
        if ($proposal->status === 'selesai') {
            return back()->with('error', 'Proposal ini telah berstatus Selesai dan tidak memerlukan revisi tambahan.');
        }

        $validated = $request->validate([
            'revision_file'  => ['required', 'file', 'mimes:pdf', 'max:10240'], // Maks. 10MB
            'catatan_revisi' => ['nullable', 'string', 'max:1000'],
        ], [
            'revision_file.required' => 'Berkas PDF naskah revisi wajib diunggah.',
            'revision_file.mimes'    => 'Format berkas revisi harus berupa dokumen PDF.',
            'revision_file.max'      => 'Ukuran berkas PDF maksimal 10 MB.',
        ]);

        $filePath = null;

        try {
            DB::transaction(function () use ($request, $proposal, $validated, &$filePath) {
                // Simpan berkas PDF revisi ke storage
                $filePath = $request->file('revision_file')->store('proposals', 'public');

                // Hitung nomor versi selanjutnya (v2, v3, dst.)
                $currentMaxVersion = $proposal->documents()->max('version') ?? 1;
                $nextVersion = $currentMaxVersion + 1;

                // Catat ke proposal_documents
                $proposal->documents()->create([
                    'version'        => $nextVersion,
                    'file_path'      => $filePath,
                    'catatan_revisi' => $validated['catatan_revisi'] ?: 'Naskah revisi versi ' . $nextVersion,
                ]);

                // Perbarui file_path utama proposal dan kembalikan status ke 'sedang_dibimbing'
                $proposal->update([
                    'file_path' => $filePath,
                    'status'    => 'sedang_dibimbing',
                ]);
            });

            return redirect()->route('mahasiswa.dashboard')
                ->with('success', 'Naskah revisi proposal berhasil diunggah! Status usulan kini kembali "Sedang Dibimbing" oleh dosen pembimbing.');
        } catch (\Throwable $th) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return back()->with('error', 'Gagal mengunggah berkas revisi: ' . $th->getMessage());
        }
    }

    /**
     * Menandai atau membatalkan status perbaikan (is_resolved) catatan review oleh mahasiswa via AJAX
     */
    public function toggleResolveReview(Request $request, \App\Models\ProposalReview $review): \Illuminate\Http\JsonResponse
    {
        // Validasi: Mahasiswa yang login harus merupakan ketua dari proposal yang bersangkutan
        if ($review->proposal->ketua_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda bukan ketua tim dari proposal ini.'
            ], \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
        }

        $newStatus = !$review->is_resolved;
        $catatanMahasiswa = $request->input('catatan_mahasiswa', $review->catatan_mahasiswa);

        $review->update([
            'is_resolved'       => $newStatus,
            'catatan_mahasiswa' => $catatanMahasiswa,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => $newStatus ? 'Poin catatan review ditandai telah diperbaiki.' : 'Tanda perbaikan dibatalkan.',
            'is_resolved' => $newStatus,
            'review_id'   => $review->id,
        ]);
    }
}
