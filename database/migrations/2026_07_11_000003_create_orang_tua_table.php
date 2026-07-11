<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa', 'id')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->string('nomor_wa', 20);
            $table->enum('hubungan', ['ayah', 'ibu', 'wali']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_tua');
    }
};
