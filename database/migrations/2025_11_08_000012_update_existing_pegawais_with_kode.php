<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Pegawai;

return new class extends Migration
{
    public function up()
    {
        // Update existing records with kode_pegawai if they don't have one
        $pegawais = Pegawai::whereNull('kode_pegawai')->get();
        
        foreach ($pegawais as $pegawai) {
            $pegawai->kode_pegawai = 'PGW' . str_pad($pegawai->id, 4, '0', STR_PAD_LEFT);
            $pegawai->save();
        }
    }

    public function down()
    {
        // This is a one-way migration
    }
};
