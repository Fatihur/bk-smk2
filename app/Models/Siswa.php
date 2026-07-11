<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'siswa';
    protected $fillable = ['nisn', 'nama', 'id_kelas'];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function orangTua(): HasMany
    {
        return $this->hasMany(OrangTua::class, 'id_siswa');
    }

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'id_siswa');
    }

    public function suratTeguran(): HasMany
    {
        return $this->hasMany(SuratTeguran::class, 'id_siswa');
    }
}
