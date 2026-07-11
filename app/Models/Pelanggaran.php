<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pelanggaran extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'pelanggaran';
    protected $fillable = ['id_siswa', 'id_jenis', 'tanggal', 'keterangan'];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisPelanggaran::class, 'id_jenis');
    }
}
