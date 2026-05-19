<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix unique constraints to include user_id for multi-tenant isolation
     */
    public function up(): void
    {
        // Fix kategori_asets - allow same name per user
        DB::statement('ALTER TABLE kategori_asets DROP INDEX IF EXISTS kategori_asets_nama_unique');
        DB::statement('ALTER TABLE kategori_asets ADD UNIQUE KEY kategori_asets_user_id_nama_unique (user_id, nama)');
        
        // Fix satuans - allow same name per user
        DB::statement('ALTER TABLE satuans DROP INDEX IF EXISTS satuans_nama_unique');
        DB::statement('ALTER TABLE satuans ADD UNIQUE KEY satuans_user_id_nama_unique (user_id, nama)');
        
        // Fix produks - allow same name per user
        DB::statement('ALTER TABLE produks DROP INDEX IF EXISTS produks_nama_produk_unique');
        DB::statement('ALTER TABLE produks ADD UNIQUE KEY produks_user_id_nama_produk_unique (user_id, nama_produk)');
        
        // Fix vendors - allow same name per user
        DB::statement('ALTER TABLE vendors DROP INDEX IF EXISTS vendors_nama_vendor_unique');
        DB::statement('ALTER TABLE vendors ADD UNIQUE KEY vendors_user_id_nama_vendor_unique (user_id, nama_vendor)');
        
        // Fix bahan_bakus - allow same name per user
        DB::statement('ALTER TABLE bahan_bakus DROP INDEX IF EXISTS bahan_bakus_nama_bahan_unique');
        DB::statement('ALTER TABLE bahan_bakus ADD UNIQUE KEY bahan_bakus_user_id_nama_bahan_unique (user_id, nama_bahan)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old constraints (if needed)
        DB::statement('ALTER TABLE kategori_asets DROP INDEX IF EXISTS kategori_asets_user_id_nama_unique');
        DB::statement('ALTER TABLE satuans DROP INDEX IF EXISTS satuans_user_id_nama_unique');
        DB::statement('ALTER TABLE produks DROP INDEX IF EXISTS produks_user_id_nama_produk_unique');
        DB::statement('ALTER TABLE vendors DROP INDEX IF EXISTS vendors_user_id_nama_vendor_unique');
        DB::statement('ALTER TABLE bahan_bakus DROP INDEX IF EXISTS bahan_bakus_user_id_nama_bahan_unique');
    }
};
