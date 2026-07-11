<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'kelas';
    protected $fillable = ['nama_kelas', 'tingkat'];

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'id_kelas');
    }
}
