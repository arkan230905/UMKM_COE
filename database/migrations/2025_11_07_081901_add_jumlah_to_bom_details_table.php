<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bom_details', function (Blueprint $table) {
            // ✅ Tambahkan kolom 'jumlah' jika belum ada
            if (!Schema::hasColumn('bom_details', 'jumlah')) {
                $table->decimal('jumlah', 15, 2)->default(0)->after('bahan_baku_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bom_details', function (Blueprint $table) {
            // ✅ Hapus kolom kalau rollback
            if (Schema::hasColumn('bom_details', 'jumlah')) {
                $table->dropColumn('jumlah');
            }
        });
    }
};
