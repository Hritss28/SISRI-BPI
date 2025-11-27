<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'type',
        'kode',
        'nama',
    ];

    /**
     * Get the parent unit.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    /**
     * Get the children units.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }

    /**
     * Get mahasiswa in this prodi.
     */
    public function mahasiswa(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'prodi_id');
    }

    /**
     * Get dosen in this prodi.
     */
    public function dosen(): HasMany
    {
        return $this->hasMany(Dosen::class, 'prodi_id');
    }

    /**
     * Get bidang minat in this prodi.
     */
    public function bidangMinat(): HasMany
    {
        return $this->hasMany(BidangMinat::class, 'prodi_id');
    }

    /**
     * Get koordinator prodi.
     */
    public function koordinatorProdi(): HasMany
    {
        return $this->hasMany(KoordinatorProdi::class, 'prodi_id');
    }

    /**
     * Get jadwal sidang.
     */
    public function jadwalSidang(): HasMany
    {
        return $this->hasMany(JadwalSidang::class, 'prodi_id');
    }

    /**
     * Scope for fakultas.
     */
    public function scopeFakultas($query)
    {
        return $query->where('type', 'fakultas');
    }

    /**
     * Scope for jurusan.
     */
    public function scopeJurusan($query)
    {
        return $query->where('type', 'jurusan');
    }

    /**
     * Scope for prodi.
     */
    public function scopeProdi($query)
    {
        return $query->where('type', 'prodi');
    }
}
