<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BimbinganHistory extends Model
{
    use HasFactory;

    protected $table = 'bimbingan_histories';

    protected $fillable = [
        'bimbingan_id',
        'status',
        'aksi',
        'catatan',
        'oleh',
        'file',
    ];

    public function bimbingan(): BelongsTo
    {
        return $this->belongsTo(Bimbingan::class);
    }
}
