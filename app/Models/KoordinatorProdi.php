<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KoordinatorProdi extends Model
{
    use HasFactory;

    protected $table = 'koordinator_prodi';

    protected $fillable = [
        'dosen_id',
        'prodi_id',
        'tahun_mulai',
        'tahun_selesai',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the dosen.
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    /**
     * Get the prodi.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'prodi_id');
    }

    /**
     * Scope for active koordinator.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
