<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            // Hapus kolom-kolom yang tidak digunakan lagi
            if (Schema::hasColumn('boms', 'bahan_baku_id')) {
                $table->dropColumn('bahan_baku_id');
            }
            if (Schema::hasColumn('boms', 'jumlah')) {
                $table->dropColumn('jumlah');
            }
            if (Schema::hasColumn('boms', 'satuan_resep')) {
                $table->dropColumn('satuan_resep');
            }
            if (Schema::hasColumn('boms', 'persentase_keuntungan')) {
                $table->dropColumn('persentase_keuntungan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            // Restore kolom jika rollback
            $table->foreignId('bahan_baku_id')->nullable()->after('produk_id');
            $table->decimal('jumlah', 15, 2)->nullable()->after('bahan_baku_id');
            $table->string('satuan_resep', 50)->nullable()->after('jumlah');
            $table->decimal('persentase_keuntungan', 5, 2)->nullable()->after('total_hpp');
        });
    }
};
