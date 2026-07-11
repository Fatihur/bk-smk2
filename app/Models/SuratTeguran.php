<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratTeguran extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'surat_teguran';
    protected $fillable = ['id_siswa', 'tingkat', 'total_poin', 'file_pdf', 'tanggal_terbit', 'status_terkirim'];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}
