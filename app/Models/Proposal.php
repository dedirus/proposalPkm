<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'ketua_id',
        'dosen_id',
        'judul_pkm',
        'skema_pkm',
        'file_path',
        'status',
    ];

    /**
     * Relasi ke ketua tim (User dengan role mahasiswa)
     */
    public function ketua(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_id');
    }

    /**
     * Relasi ke dosen pembimbing (User dengan role dosen, nullable)
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    /**
     * Relasi ke anggota tim proposal
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProposalMember::class, 'proposal_id');
    }

    /**
     * Relasi ke catatan review dosen
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProposalReview::class, 'proposal_id')->latest();
    }

    /**
     * Relasi ke seluruh riwayat versi dokumen proposal
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ProposalDocument::class, 'proposal_id')->orderByDesc('version');
    }

    /**
     * Relasi ke dokumen versi paling mutakhir
     */
    public function latestDocument(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProposalDocument::class, 'proposal_id')->latestOfMany('version');
    }

    /**
     * Accessor untuk teks status pembimbing
     */
    public function getStatusPembimbingAttribute(): string
    {
        if (is_null($this->dosen_id)) {
            return 'Mencari Pembimbing';
        }

        return 'Sudah Ada Pembimbing: ' . ($this->dosen?->name ?? '-');
    }
}
