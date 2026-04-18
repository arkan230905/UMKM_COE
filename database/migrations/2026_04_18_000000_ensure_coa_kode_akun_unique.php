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
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // First, clean up any existing duplicates
        $duplicates = DB::table('coas')
            ->select('kode_akun', DB::raw('MIN(id) as keep_id'))
            ->groupBy('kode_akun')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            // Delete all records with this kode_akun except the one with the smallest ID
            DB::table('coas')
                ->where('kode_akun', $duplicate->kode_akun)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        // Drop the unique index if it exists (to avoid errors)
        $indexes = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_akun_unique'");
        if (!empty($indexes)) {
            Schema::table('coas', function (Blueprint $table) {
                $table->dropUnique(['kode_akun']);
            });
        }

        // Re-add the unique constraint
        Schema::table('coas', function (Blueprint $table) {
            $table->unique('kode_akun');
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            $table->dropUnique(['kode_akun']);
        });
    }
};
