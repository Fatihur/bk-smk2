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
            'email' => 'gurubk@smkn2sumbawa.sch.id',
            'password' => bcrypt('password'),
            'role' => 'guru_bk',
        ]);
        DB::table('users')->insert([
            'nama' => 'Kepala Sekolah',
            'email' => 'kepsek@smkn2sumbawa.sch.id',
            'password' => bcrypt('password'),
            'role' => 'kepala_sekolah',
        ]);
    }
}
