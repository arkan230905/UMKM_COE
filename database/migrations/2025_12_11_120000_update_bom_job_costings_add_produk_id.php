<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus kolom lama yang tidak diperlukan
        Schema::table('bom_job_costings', function (Blueprint $table) {
            // Cek dan hapus kolom lama jika ada
            if (Schema::hasColumn('bom_job_costings', 'kode_bom')) {
                $table->dropColumn('kode_bom');
            }
            if (Schema::hasColumn('bom_job_costings', 'nama_bom')) {
                $table->dropColumn('nama_bom');
            }
            if (Schema::hasColumn('bom_job_costings', 'deskripsi')) {
                $table->dropColumn('deskripsi');
            }
            if (Schema::hasColumn('bom_job_costings', 'periode')) {
                $table->dropColumn('periode');
            }
            if (Schema::hasColumn('bom_job_costings', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
        
        // Tambah kolom produk_id jika belum ada
        if (!Schema::hasColumn('bom_job_costings', 'produk_id')) {
            Schema::table('bom_job_costings', function (Blueprint $table) {
                $table->foreignId('produk_id')->after('id')->unique()->constrained('produks')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bom_job_costings', function (Blueprint $table) {
            if (Schema::hasColumn('bom_job_costings', 'produk_id')) {
                $table->dropForeign(['produk_id']);
                $table->dropColumn('produk_id');
            }
            
            $table->string('kode_bom')->unique()->after('id');
            $table->string('nama_bom')->after('kode_bom');
            $table->text('deskripsi')->nullable()->after('nama_bom');
            $table->string('periode', 7)->nullable()->after('hpp_per_unit');
            $table->boolean('is_active')->default(true)->after('periode');
        });
    }
};
