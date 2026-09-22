<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nip_nim',
        'kepakaran',
        'skema_pkm',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi untuk Mahasiswa (sebagai Ketua Tim yang mengajukan proposal)
     */
    public function proposalsKetua(): HasMany
    {
        return $this->hasMany(Proposal::class, 'ketua_id');
    }

    /**
     * Relasi untuk Dosen (proposal bimbingan)
     */
    public function proposalsBimbingan(): HasMany
    {
        return $this->hasMany(Proposal::class, 'dosen_id');
    }

    /**
     * Relasi catatan review yang dibuat oleh dosen
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProposalReview::class, 'dosen_id');
    }

    /**
     * Helper Role Check
     */
    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Memeriksa apakah dosen memiliki wewenang untuk mereview skema tertentu
     */
    public function canReviewSkema(?string $skema): bool
    {
        if (!$this->isDosen()) {
            return false;
        }

        if (empty($this->skema_pkm) || strtolower($this->skema_pkm) === 'all') {
            return true;
        }

        if (empty($skema)) {
            return false;
        }

        $dosenCode = strtoupper(explode(' ', trim($this->skema_pkm))[0]);
        $skemaCode = strtoupper(explode(' ', trim($skema))[0]);

        return $dosenCode === $skemaCode || strtoupper(trim($this->skema_pkm)) === strtoupper(trim($skema));
    }
}
