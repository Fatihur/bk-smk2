<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa', 'id')->cascadeOnDelete();
            $table->foreignId('id_jenis')->constrained('jenis_pelanggaran', 'id');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran');
    }
};
