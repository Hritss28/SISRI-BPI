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
        Schema::create('pelaksanaan_sidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_sidang_id')->constrained('pendaftaran_sidang')->cascadeOnDelete();
            $table->dateTime('tanggal_sidang');
            $table->string('tempat', 100)->nullable();
            $table->enum('status', ['dijadwalkan', 'selesai', 'dibatalkan'])->default('dijadwalkan');
            $table->string('berita_acara', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelaksanaan_sidang');
    }
};
