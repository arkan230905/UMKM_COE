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
        // Disable foreign key checks temporarily untuk safe cleanup
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Hapus tabel yang tidak digunakan untuk menyederhanakan struktur BOP
        Schema::dropIfExists('bops');
        Schema::dropIfExists('komponen_bops');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Perbaiki struktur bop_proses untuk lebih sederhana
        // Tambahkan kolom budget dan aktual jika belum ada
        if (!Schema::hasColumn('bop_proses', 'budget')) {
            Schema::table('bop_proses', function (Blueprint $table) {
                $table->decimal('budget', 15, 2)->default(0)->after('bop_per_unit');
                $table->decimal('aktual', 15, 2)->default(0)->after('budget');
            });
        }
        
        // Hapus kolom komponen_bop JSON yang tidak perlu
        if (Schema::hasColumn('bop_proses', 'komponen_bop')) {
            Schema::table('bop_proses', function (Blueprint $table) {
                $table->dropColumn('komponen_bop');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan struktur semula jika rollback
        Schema::create('bops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('kode_akun', 50);
            $table->string('nama_akun', 255);
            $table->decimal('budget', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->decimal('aktual', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        Schema::create('komponen_bops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('kode_komponen', 50);
            $table->string('nama_komponen', 255);
            $table->string('satuan', 50);
            $table->decimal('tarif_per_satuan', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Tambah kembali kolom komponen_bop
        if (!Schema::hasColumn('bop_proses', 'komponen_bop')) {
            Schema::table('bop_proses', function (Blueprint $table) {
                $table->json('komponen_bop')->nullable()->after('lain_lain_per_jam');
            });
        }
    }
};
