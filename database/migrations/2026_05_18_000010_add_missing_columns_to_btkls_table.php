<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add missing columns to btkls table
     */
    public function up(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            // Add satuan column if it doesn't exist
            if (!Schema::hasColumn('btkls', 'satuan')) {
                $table->enum('satuan', ['Jam', 'Unit', 'Batch'])->default('Jam')->after('tarif_btkl');
            }
            // Add kapasitas_per_jam column if it doesn't exist
            if (!Schema::hasColumn('btkls', 'kapasitas_per_jam')) {
                $table->integer('kapasitas_per_jam')->default(0)->after('satuan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            $table->dropColumn(['satuan', 'kapasitas_per_jam']);
        });
    }
};
