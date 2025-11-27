<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisiSidang extends Model
{
    use HasFactory;

    protected $table = 'revisi_sidang';

    protected $fillable = [
        'pelaksanaan_sidang_id',
        'dosen_id',
        'file_revisi',
        'catatan',
        'status',
        'tanggal_submit',
        'tanggal_validasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_submit' => 'datetime',
            'tanggal_validasi' => 'datetime',
        ];
    }

    /**
     * Get the pelaksanaan sidang.
     */
    public function pelaksanaanSidang(): BelongsTo
    {
        return $this->belongsTo(PelaksanaanSidang::class);
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
}
