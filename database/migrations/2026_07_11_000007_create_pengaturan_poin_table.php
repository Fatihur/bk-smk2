<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_poin', function (Blueprint $table) {
            $table->id();
            $table->enum('tingkat', ['sp1', 'sp2', 'sp3']);
            $table->integer('batas_poin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_poin');
    }
};
