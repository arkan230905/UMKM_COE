<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('coas')
            ->whereIn('nama_akun', ['BOP - Biaya Penyusutan Mesin', 'BOP - Beban Penyusutan Mesin', 'BOP TL - Biaya Penyusutan Mesin'])
            ->update([
                'nama_akun' => 'BOP - Penyusutan Mesin',
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('coas')
            ->where('nama_akun', 'BOP - Penyusutan Mesin')
            ->update([
                'nama_akun' => 'BOP - Biaya Penyusutan Mesin',
                'updated_at' => now()
            ]);
    }
};
