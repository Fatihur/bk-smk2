<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nisn', 20)->unique();
            $table->string('nama', 100);
            $table->foreignId('id_kelas')->constrained('kelas', 'id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
