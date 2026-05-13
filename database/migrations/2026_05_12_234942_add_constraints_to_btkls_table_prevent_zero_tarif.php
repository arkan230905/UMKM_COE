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
        // Gunakan pengecekan di luar Schema::table untuk keamanan DB::statement
        if (DB::getDriverName() === 'mysql') {
            
            // Cek kolom tarif_btkl
            if (Schema::hasColumn('btkls', 'tarif_btkl')) {
                // Gunakan TRY-CATCH agar jika constraint sudah ada tidak menyebabkan FAIL
                try {
                    DB::statement('ALTER TABLE btkls ADD CONSTRAINT chk_tarif_btkl_positive CHECK (tarif_btkl >= 0)');
                } catch (\Exception $e) {
                    // Abaikan jika constraint sudah ada
                }
            }

            // Cek kolom kapasitas_per_jam (Penyebab eror sebelumnya)
            if (Schema::hasColumn('btkls', 'kapasitas_per_jam')) {
                try {
                    DB::statement('ALTER TABLE btkls ADD CONSTRAINT chk_kapasitas_per_jam_positive CHECK (kapasitas_per_jam > 0)');
                } catch (\Exception $e) {
                    // Abaikan jika constraint sudah ada
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE btkls DROP CONSTRAINT chk_tarif_btkl_positive');
            } catch (\Exception $e) { }

            try {
                DB::statement('ALTER TABLE btkls DROP CONSTRAINT chk_kapasitas_per_jam_positive');
            } catch (\Exception $e) { }
        }
    }
};