<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename tarif_btkl to tarif_per_jam to match model expectations
     */
    public function up(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            // Rename tarif_btkl to tarif_per_jam if tarif_btkl exists and tarif_per_jam doesn't
            if (Schema::hasColumn('btkls', 'tarif_btkl') && !Schema::hasColumn('btkls', 'tarif_per_jam')) {
                $table->renameColumn('tarif_btkl', 'tarif_per_jam');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            // Rename back
            if (Schema::hasColumn('btkls', 'tarif_per_jam') && !Schema::hasColumn('btkls', 'tarif_btkl')) {
                $table->renameColumn('tarif_per_jam', 'tarif_btkl');
            }
        });
    }
};
