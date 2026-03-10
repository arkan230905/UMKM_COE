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
        // Check if columns exist before updating
        if (Schema::hasColumn('pegawais', 'gaji_pokok') && Schema::hasColumn('pegawais', 'gaji')) {
            DB::table('pegawais')
                ->whereNull('gaji_pokok')
                ->whereNotNull('gaji')
                ->update(['gaji_pokok' => DB::raw('gaji')]);
        }
        
        if (Schema::hasColumn('pegawais', 'tarif_lembur') && Schema::hasColumn('pegawais', 'tarif')) {
            DB::table('pegawais')
                ->whereNull('tarif_lembur')
                ->whereNotNull('tarif')
                ->update(['tarif_lembur' => DB::raw('tarif')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the changes - copy back from new fields to old fields if they exist
        DB::table('pegawais')
            ->whereNull('gaji')
            ->whereNotNull('gaji_pokok')
            ->update(['gaji' => DB::raw('gaji_pokok')]);
            
        DB::table('pegawais')
            ->whereNull('tarif')
            ->whereNotNull('tarif_lembur')
            ->update(['tarif' => DB::raw('tarif_lembur')]);
    }
};
