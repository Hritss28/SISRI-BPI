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
        Schema::create('usulan_pembimbing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topik_id')->constrained('topik_skripsi')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosen')->cascadeOnDelete();
            $table->tinyInteger('urutan');
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->date('jangka_waktu')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_respon')->nullable();
            $table->timestamps();
            
            $table->unique(['topik_id', 'urutan'], 'unique_topik_urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usulan_pembimbing');
    }
};
