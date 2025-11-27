<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'user_id',
        'nim',
        'nama',
        'prodi_id',
        'angkatan',
        'email',
        'no_hp',
    ];

    /**
     * Get the user that owns the mahasiswa.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the prodi of the mahasiswa.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'prodi_id');
    }

    /**
     * Get the topik skripsi.
     */
    public function topikSkripsi(): HasMany
    {
        return $this->hasMany(TopikSkripsi::class);
    }
}
