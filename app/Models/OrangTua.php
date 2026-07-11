<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrangTua extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'orang_tua';
    protected $fillable = ['id_siswa', 'nama', 'nomor_wa', 'hubungan'];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}
