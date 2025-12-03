<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bimbingan_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bimbingan_id')->constrained('bimbingan')->cascadeOnDelete();
            $table->string('status'); // menunggu, direvisi, disetujui, ditolak
            $table->string('aksi'); // diajukan, direspon, upload_revisi
            $table->text('catatan')->nullable();
            $table->string('oleh'); // mahasiswa, dosen
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bimbingan_histories');
    }
};
