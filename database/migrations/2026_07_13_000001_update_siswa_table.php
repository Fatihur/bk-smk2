<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            // Drop foreign key first, then column
            $table->dropForeign(['id_kelas']);
            $table->dropColumn('id_kelas');

            // Rename nama to nama_siswa
            $table->renameColumn('nama', 'nama_siswa');

            // Add new columns
            $table->enum('jk', ['L', 'P'])->after('nama_siswa');
            $table->string('tempat_lahir', 50)->after('nisn');
            $table->date('tgl_lahir')->nullable()->after('tempat_lahir');
            $table->string('nik', 20)->after('tgl_lahir');
            $table->string('agama', 20)->after('nik');
            $table->text('alamat')->after('agama');
            $table->string('hp', 20)->nullable()->after('alamat');
            $table->string('ayah', 100)->nullable()->after('hp');
            $table->string('ibu', 100)->nullable()->after('ayah');
            $table->string('no_wali', 20)->nullable()->after('ibu');
            $table->enum('rombel', ['X KJJ', 'XI KJJ', 'XII KJJ'])->after('no_wali');
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn(['jk', 'tempat_lahir', 'tgl_lahir', 'nik', 'agama', 'alamat', 'hp', 'ayah', 'ibu', 'no_wali', 'rombel']);
            $table->renameColumn('nama_siswa', 'nama');
            $table->foreignId('id_kelas')->constrained('kelas', 'id');
        });
    }
};
