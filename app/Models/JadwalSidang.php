<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalSidang extends Model
{
    use HasFactory;

    protected $table = 'jadwal_sidang';

    protected $fillable = [
        'prodi_id',
        'periode_id',
        'jenis',
        'nama',
        'deskripsi',
        'tanggal_buka',
        'tanggal_tutup',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_buka' => 'datetime',
            'tanggal_tutup' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the prodi.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'prodi_id');
    }

    /**
     * Get the periode.
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    /**
     * Get pendaftaran sidang.
     */
    public function pendaftaranSidang(): HasMany
    {
        return $this->hasMany(PendaftaranSidang::class);
    }

    /**
     * Scope for active.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for seminar proposal.
     */
    public function scopeSeminarProposal($query)
    {
        return $query->where('jenis', 'seminar_proposal');
    }

    /**
     * Scope for sidang skripsi.
     */
    public function scopeSidangSkripsi($query)
    {
        return $query->where('jenis', 'sidang_skripsi');
    }

    /**
     * Check if registration is open.
     */
    public function isOpen(): bool
    {
        $now = now();
        return $this->is_active && $now >= $this->tanggal_buka && $now <= $this->tanggal_tutup;
    }
}
