<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('orang_tua');
        Schema::dropIfExists('kelas');
    }

    public function down(): void
    {
        // recreate kelas table
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas', 50);
            $table->enum('tingkat', ['X', 'XI', 'XII']);
        });

        // recreate orang_tua table
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa', 'id')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->string('nomor_wa', 20);
            $table->enum('hubungan', ['ayah', 'ibu', 'wali']);
        });
    }
};
