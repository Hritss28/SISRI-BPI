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
        Schema::create('revisi_sidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelaksanaan_sidang_id')->constrained('pelaksanaan_sidang')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosen')->cascadeOnDelete();
            $table->string('file_revisi', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'revisi_ulang'])->default('menunggu');
            $table->timestamp('tanggal_submit')->useCurrent();
            $table->timestamp('tanggal_validasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revisi_sidang');
    }
};
