<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'nama_anggota',
        'nim_anggota',
        'role_anggota',
        'jobdesk',
    ];

    /**
     * Relasi ke proposal induk
     */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
