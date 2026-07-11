<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_teguran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa', 'id')->cascadeOnDelete();
            $table->enum('tingkat', ['sp1', 'sp2', 'sp3']);
            $table->integer('total_poin');
            $table->string('file_pdf', 255);
            $table->date('tanggal_terbit');
            $table->boolean('status_terkirim')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_teguran');
    }
};
