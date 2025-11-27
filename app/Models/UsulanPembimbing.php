<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsulanPembimbing extends Model
{
    use HasFactory;

    protected $table = 'usulan_pembimbing';

    protected $fillable = [
        'topik_id',
        'dosen_id',
        'urutan',
        'status',
        'jangka_waktu',
        'catatan',
        'tanggal_respon',
    ];

    protected function casts(): array
    {
        return [
            'jangka_waktu' => 'date',
            'tanggal_respon' => 'datetime',
        ];
    }

    /**
     * Get the topik.
     */
    public function topik(): BelongsTo
    {
        return $this->belongsTo(TopikSkripsi::class, 'topik_id');
    }

    /**
     * Get the dosen.
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    /**
     * Scope for pending.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'menunggu');
    }

    /**
     * Scope for approved.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'diterima');
    }
}
