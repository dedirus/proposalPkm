<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'version',
        'file_path',
        'catatan_revisi',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
        ];
    }

    /**
     * Relasi ke proposal induk
     */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
