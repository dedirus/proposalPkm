<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\ProposalDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ProposalDocumentController extends Controller
{
    /**
     * Menampilkan atau mengalirkan berkas PDF naskah utama / mutakhir dari proposal
     */
    public function showMainDocument(Request $request, Proposal $proposal): Response
    {
        $this->ensureAuthorized($request->user(), $proposal);

        $filePath = $proposal->documents()->orderByDesc('version')->first()?->file_path ?? $proposal->file_path;

        return $this->streamPdfResponse($filePath, 'proposal-'.$proposal->id.'.pdf');
    }

    /**
     * Menampilkan atau mengalirkan berkas PDF riwayat versi tertentu dari proposal
     */
    public function showVersionDocument(Request $request, Proposal $proposal, ProposalDocument $document): Response
    {
        if ($document->proposal_id !== $proposal->id) {
            abort(Response::HTTP_NOT_FOUND, 'Dokumen tidak sesuai dengan usulan proposal.');
        }

        $this->ensureAuthorized($request->user(), $proposal);

        return $this->streamPdfResponse($document->file_path, 'proposal-'.$proposal->id.'-v'.$document->version.'.pdf');
    }

    /**
     * Validasi otorisasi akses terhadap dokumen proposal
     */
    private function ensureAuthorized(User $user, Proposal $proposal): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isMahasiswa()) {
            $isOwner = $proposal->ketua_id === $user->id;
            $isMember = $proposal->members()->where('nim_anggota', $user->nip_nim)->exists();

            if ($isOwner || $isMember) {
                return;
            }

            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Anda tidak memiliki wewenang untuk membuka dokumen usulan ini.');
        }

        if ($user->isDosen()) {
            if ($proposal->dosen_id === $user->id) {
                return;
            }

            if ($proposal->dosen_id === null && $proposal->status === 'diajukan' && $user->canReviewSkema($proposal->skema_pkm)) {
                return;
            }

            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Anda bukan dosen pembimbing proposal ini atau usulan tidak sesuai skema bimbingan Anda.');
        }

        abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
    }

    /**
     * Mengalirkan respon file PDF secara aman baik dari disk private ('local') maupun disk legacy ('public')
     */
    private function streamPdfResponse(?string $filePath, string $downloadName): Response
    {
        if (empty($filePath)) {
            abort(Response::HTTP_NOT_FOUND, 'Path berkas tidak ditemukan.');
        }

        $disk = null;
        if (Storage::disk('local')->exists($filePath)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($filePath)) {
            $disk = 'public';
        }

        if ($disk === null) {
            abort(Response::HTTP_NOT_FOUND, 'Berkas fisik naskah proposal tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk($disk)->response($filePath, $downloadName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"',
        ]);
    }
}
