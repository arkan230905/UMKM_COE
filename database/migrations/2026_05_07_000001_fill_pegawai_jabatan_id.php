<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fill jabatan_id for all pegawais based on jabatan name
        $pegawais = \App\Models\Pegawai::whereNull('jabatan_id')
            ->orWhere('jabatan_id', 0)
            ->get();
        
        foreach ($pegawais as $pegawai) {
            if ($pegawai->jabatan) {
                // Find jabatan by name and user_id
                $jabatan = \App\Models\Jabatan::where('nama', $pegawai->jabatan)
                    ->where('user_id', $pegawai->user_id)
                    ->first();
                
                if ($jabatan) {
                    $pegawai->jabatan_id = $jabatan->id;
                    $pegawai->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse - this is a data fix
    }
};
