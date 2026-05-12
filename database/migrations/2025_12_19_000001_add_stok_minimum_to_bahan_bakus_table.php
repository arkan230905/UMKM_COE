<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah kolom sudah ada
        $hasColumn = DB::select("SHOW COLUMNS FROM bahan_bakus LIKE 'stok_minimum'");
        
        if (empty($hasColumn)) {
            DB::statement("ALTER TABLE bahan_bakus ADD COLUMN stok_minimum DECIMAL(15,4) DEFAULT 0 AFTER stok");
            
            // Set default value untuk data yang sudah ada
            DB::statement("UPDATE bahan_bakus SET stok_minimum = 10 WHERE stok_minimum = 0");
        }
    }

    public function down(): void
    {
        $hasColumn = DB::select("SHOW COLUMNS FROM bahan_bakus LIKE 'stok_minimum'");
        
        if (!empty($hasColumn)) {
            DB::statement("ALTER TABLE bahan_bakus DROP COLUMN stok_minimum");
        }
    }
};