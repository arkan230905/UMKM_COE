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
        // Drop unique constraint lama pada kode_jabatan
        Schema::table('jabatans', function (Blueprint $table) {
            // Cari nama index/constraint yang ada
            $indexes = DB::select("SHOW INDEX FROM jabatans WHERE Column_name = 'kode_jabatan' AND Non_unique = 0");
            
            foreach ($indexes as $index) {
                try {
                    $table->dropUnique($index->Key_name);
                } catch (\Exception $e) {
                    // Ignore jika sudah tidak ada
                }
            }
        });
        
        // Tambah unique constraint baru: kode_jabatan + user_id
        Schema::table('jabatans', function (Blueprint $table) {
            $table->unique(['kode_jabatan', 'user_id'], 'jabatans_kode_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop unique constraint baru
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropUnique('jabatans_kode_user_unique');
        });
        
        // Kembalikan unique constraint lama (hati-hati, ini bisa error jika ada duplicate)
        Schema::table('jabatans', function (Blueprint $table) {
            $table->unique('kode_jabatan');
        });
    }
};
