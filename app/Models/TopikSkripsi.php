<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TopikSkripsi extends Model
{
    use HasFactory;

    protected $table = 'topik_skripsi';

    protected $fillable = [
        'mahasiswa_id',
        'bidang_minat_id',
        'judul',
        'file_proposal',
        'status',
        'catatan',
    ];

    /**
     * Get the mahasiswa.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Get the bidang minat.
     */
    public function bidangMinat(): BelongsTo
    {
        return $this->belongsTo(BidangMinat::class);
    }

    /**
     * Get usulan pembimbing.
     */
    public function usulanPembimbing(): HasMany
    {
        return $this->hasMany(UsulanPembimbing::class, 'topik_id');
    }

    /**
     * Alias for usulanPembimbing (plural form).
     */
    public function usulanPembimbings(): HasMany
    {
        return $this->hasMany(UsulanPembimbing::class, 'topik_id');
    }

    /**
     * Get bimbingan.
     */
    public function bimbingan(): HasMany
    {
        return $this->hasMany(Bimbingan::class, 'topik_id');
    }

    /**
     * Get pendaftaran sidang.
     */
    public function pendaftaranSidang(): HasMany
    {
        return $this->hasMany(PendaftaranSidang::class, 'topik_id');
    }

    /**
     * Get pembimbing 1 relationship (usulan yang diterima dengan urutan 1).
     */
    public function usulanPembimbing1()
    {
        return $this->hasOne(UsulanPembimbing::class, 'topik_id')
            ->where('urutan', 1)
            ->where('status', 'diterima');
    }

    /**
     * Get pembimbing 2 relationship (usulan yang diterima dengan urutan 2).
     */
    public function usulanPembimbing2()
    {
        return $this->hasOne(UsulanPembimbing::class, 'topik_id')
            ->where('urutan', 2)
            ->where('status', 'diterima');
    }

    /**
     * Get pembimbing 1 (helper method).
     */
    public function pembimbing1()
    {
        return $this->usulanPembimbing()->where('urutan', 1)->where('status', 'diterima')->first()?->dosen;
    }

    /**
     * Get pembimbing 2 (helper method).
     */
    public function pembimbing2()
    {
        return $this->usulanPembimbing()->where('urutan', 2)->where('status', 'diterima')->first()?->dosen;
    }

    /**
     * Get pembimbing1_id accessor.
     */
    public function getPembimbing1IdAttribute()
    {
        return $this->usulanPembimbing()->where('urutan', 1)->where('status', 'diterima')->first()?->dosen_id;
    }

    /**
     * Get pembimbing2_id accessor.
     */
    public function getPembimbing2IdAttribute()
    {
        return $this->usulanPembimbing()->where('urutan', 2)->where('status', 'diterima')->first()?->dosen_id;
    }

    /**
     * Scope for pending topik.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'menunggu');
    }

    /**
     * Scope for approved topik.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'diterima');
    }
}
