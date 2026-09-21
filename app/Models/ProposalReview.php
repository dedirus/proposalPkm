<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'dosen_id',
        'halaman',
        'catatan_review',
        'status_bagian',
        'is_resolved',
        'catatan_mahasiswa',
    ];

    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
        ];
    }

    /**
     * Relasi ke proposal yang direview
     */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * Relasi ke dosen pembuat review
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}
