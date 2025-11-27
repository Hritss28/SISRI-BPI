<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periode extends Model
{
    use HasFactory;

    protected $table = 'periode';

    protected $fillable = [
        'nama',
        'jenis',
        'tahun_akademik',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get jadwal sidang.
     */
    public function jadwalSidang(): HasMany
    {
        return $this->hasMany(JadwalSidang::class);
    }

    /**
     * Scope for active periode.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
