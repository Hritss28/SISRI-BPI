<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BidangMinat extends Model
{
    use HasFactory;

    protected $table = 'bidang_minat';

    protected $fillable = [
        'prodi_id',
        'nama',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
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
     * Get topik skripsi.
     */
    public function topikSkripsi(): HasMany
    {
        return $this->hasMany(TopikSkripsi::class);
    }

    /**
     * Scope for active bidang minat.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
