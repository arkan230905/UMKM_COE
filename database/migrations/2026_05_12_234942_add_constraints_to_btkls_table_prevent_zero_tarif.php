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
        Schema::table('btkls', function (Blueprint $table) {
            // Add check constraint (MySQL 8.0+)
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE btkls ADD CONSTRAINT chk_tarif_btkl_positive CHECK (tarif_btkl >= 0)');
                DB::statement('ALTER TABLE btkls ADD CONSTRAINT chk_kapasitas_per_jam_positive CHECK (kapasitas_per_jam > 0)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            // Remove check constraints
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE btkls DROP CONSTRAINT chk_tarif_btkl_positive');
                DB::statement('ALTER TABLE btkls DROP CONSTRAINT chk_kapasitas_per_jam_positive');
            }
        });
    }
};
