<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PendaftaranSidang extends Model
{
    use HasFactory;

    protected $table = 'pendaftaran_sidang';

    protected $fillable = [
        'topik_id',
        'jadwal_sidang_id',
        'jenis',
        'status_pembimbing_1',
        'status_pembimbing_2',
        'status_koordinator',
        'catatan_pembimbing_1',
        'catatan_pembimbing_2',
        'catatan_koordinator',
    ];

    /**
     * Get the topik.
     */
    public function topik(): BelongsTo
    {
        return $this->belongsTo(TopikSkripsi::class, 'topik_id');
    }

    /**
     * Get the jadwal sidang.
     */
    public function jadwalSidang(): BelongsTo
    {
        return $this->belongsTo(JadwalSidang::class);
    }

    /**
     * Get pelaksanaan sidang.
     */
    public function pelaksanaanSidang(): HasOne
    {
        return $this->hasOne(PelaksanaanSidang::class);
    }

    /**
     * Check if fully approved.
     */
    public function isFullyApproved(): bool
    {
        return $this->status_pembimbing_1 === 'disetujui' 
            && $this->status_pembimbing_2 === 'disetujui' 
            && $this->status_koordinator === 'disetujui';
    }

    /**
     * Scope for approved.
     */
    public function scopeApproved($query)
    {
        return $query->where('status_pembimbing_1', 'disetujui')
            ->where('status_pembimbing_2', 'disetujui')
            ->where('status_koordinator', 'disetujui');
    }
}
