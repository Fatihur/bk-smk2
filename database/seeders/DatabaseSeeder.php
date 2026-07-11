<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::table('users')->insert([
            'nama' => 'Guru BK',
            'email' => 'guru_bk@smkn2sumbawa.sch.id',
            'password' => bcrypt('password'),
            'role' => 'guru_bk',
        ]);
        DB::table('users')->insert([
            'nama' => 'Kepala Sekolah',
            'email' => 'kepsek@smkn2sumbawa.sch.id',
            'password' => bcrypt('password'),
            'role' => 'kepala_sekolah',
        ]);

        DB::table('pengaturan_poin')->insert([
            ['tingkat' => 'sp1', 'batas_poin' => 25],
            ['tingkat' => 'sp2', 'batas_poin' => 50],
            ['tingkat' => 'sp3', 'batas_poin' => 75],
        ]);

        DB::table('jenis_pelanggaran')->insert([
            ['nama' => 'Terlambat masuk sekolah', 'poin' => 5],
            ['nama' => 'Tidak memakai seragam lengkap', 'poin' => 10],
            ['nama' => 'Membuang sampah sembarangan', 'poin' => 5],
            ['nama' => 'Berkelahi di lingkungan sekolah', 'poin' => 25],
            ['nama' => 'Merokok di lingkungan sekolah', 'poin' => 30],
            ['nama' => 'Membawa HP saat jam pelajaran', 'poin' => 10],
            ['nama' => 'Membolos', 'poin' => 15],
            ['nama' => 'Tidak mengerjakan tugas', 'poin' => 5],
        ]);
    }
}
