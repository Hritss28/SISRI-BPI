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
        Schema::create('penguji_sidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelaksanaan_sidang_id')->constrained('pelaksanaan_sidang')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosen')->cascadeOnDelete();
            $table->enum('role', ['pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'penguji_3']);
            $table->boolean('ttd_berita_acara')->default(false);
            $table->timestamp('tanggal_ttd')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penguji_sidang');
    }
};
