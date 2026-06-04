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
        // Remove all Aset Lancar (jenis_aset_id = 2) and Aset Tidak Berwujud (jenis_aset_id = 3)
        // Keep only Aset Tetap (jenis_aset_id = 1)
        
        // Get IDs of non-fixed asset types
        $nonFixedIds = DB::table('jenis_asets')
            ->whereIn('nama', ['Aset Lancar', 'Aset Tidak Berwujud'])
            ->pluck('id')
            ->toArray();

        if (!empty($nonFixedIds)) {
            // Delete all asets associated with non-fixed asset types
            DB::table('asets')
                ->whereIn('jenis_aset_id', $nonFixedIds)
                ->delete();

            // Delete all categories associated with non-fixed asset types
            DB::table('kategori_asets')
                ->whereIn('jenis_aset_id', $nonFixedIds)
                ->delete();

            // Delete the jenis_aset entries
            DB::table('jenis_asets')
                ->whereIn('id', $nonFixedIds)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration removes data, so we cannot safely reverse it
        // If you need to restore these types, re-seed the database
    }
};
