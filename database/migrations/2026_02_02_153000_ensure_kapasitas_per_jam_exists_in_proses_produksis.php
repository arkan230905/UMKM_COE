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
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Make sure kapasitas_per_jam column exists
            if (!Schema::hasColumn('proses_produksis', 'kapasitas_per_jam')) {
                $table->integer('kapasitas_per_jam')->default(0)->after('satuan_btkl');
            }
            
            // Make sure btkl_id column exists if needed
            if (!Schema::hasColumn('proses_produksis', 'btkl_id')) {
                $table->foreignId('btkl_id')->nullable()->after('kapasitas_per_jam');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Only drop if they exist and if we're sure they're not needed
            if (Schema::hasColumn('proses_produksis', 'btkl_id')) {
                $table->dropForeign(['btkl_id']);
                $table->dropColumn('btkl_id');
            }
        });
    }
};
