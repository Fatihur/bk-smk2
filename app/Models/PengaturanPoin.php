<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanPoin extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'pengaturan_poin';
    protected $fillable = ['tingkat', 'batas_poin'];
}
