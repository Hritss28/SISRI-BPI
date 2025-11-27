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
        Schema::create('pendaftaran_sidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topik_id')->constrained('topik_skripsi')->cascadeOnDelete();
            $table->foreignId('jadwal_sidang_id')->constrained('jadwal_sidang')->cascadeOnDelete();
            $table->enum('jenis', ['seminar_proposal', 'sidang_skripsi']);
            $table->enum('status_pembimbing_1', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->enum('status_pembimbing_2', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->enum('status_koordinator', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_pembimbing_1')->nullable();
            $table->text('catatan_pembimbing_2')->nullable();
            $table->text('catatan_koordinator')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_sidang');
    }
};
