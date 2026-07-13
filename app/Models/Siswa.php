<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';
    protected $fillable = [
        'nama_siswa', 'jk', 'nisn', 'tempat_lahir', 'tgl_lahir', 'nik',
        'agama', 'alamat', 'hp', 'ayah', 'ibu', 'no_wali', 'rombel'
    ];

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'id_siswa');
    }

    public function suratTeguran(): HasMany
    {
        return $this->hasMany(SuratTeguran::class, 'id_siswa');
    }
}
