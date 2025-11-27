<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilai';

    protected $fillable = [
        'pelaksanaan_sidang_id',
        'dosen_id',
        'jenis_nilai',
        'nilai',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
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
     * Scope for bimbingan.
     */
    public function scopeBimbingan($query)
    {
        return $query->where('jenis_nilai', 'bimbingan');
    }

    /**
     * Scope for ujian.
     */
    public function scopeUjian($query)
    {
        return $query->where('jenis_nilai', 'ujian');
    }
}
