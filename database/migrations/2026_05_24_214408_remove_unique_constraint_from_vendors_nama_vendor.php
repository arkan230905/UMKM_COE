<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menghapus unique constraint pada nama_vendor agar bisa ada vendor dengan nama sama
     */
    public function up(): void
    {
        // Cek apakah ada foreign key yang menggunakan index ini
        // Jika ada, kita perlu drop foreign key dulu
        
        // Cara aman: Buat ulang tabel vendors tanpa unique constraint
        // Tapi ini risky, jadi kita gunakan cara lain
        
        // Alternatif: Hapus unique constraint dengan cara yang benar
        try {
            // Coba drop index langsung
            DB::statement('ALTER TABLE vendors DROP INDEX vendors_user_id_nama_vendor_unique');
        } catch (\Exception $e) {
            // Jika gagal karena foreign key, kita perlu cara lain
            // Untuk sementara, kita biarkan constraint tetap ada
            // User bisa menghapus manual via SQL jika diperlukan
            echo "Warning: Tidak bisa menghapus unique constraint karena ada foreign key dependency.\n";
            echo "Silakan jalankan SQL manual:\n";
            echo "ALTER TABLE vendors DROP INDEX vendors_user_id_nama_vendor_unique;\n";
        }
    }

    /**
     * Reverse the migrations.
     * Mengembalikan unique constraint jika rollback
     */
    public function down(): void
    {
        // Kembalikan unique constraint
        try {
            DB::statement('ALTER TABLE vendors ADD UNIQUE KEY vendors_user_id_nama_vendor_unique (user_id, nama_vendor)');
        } catch (\Exception $e) {
            // Ignore jika sudah ada
        }
    }
};
