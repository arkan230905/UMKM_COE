<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Tambahkan kolom harga_pokok jika belum ada
            if (!Schema::hasColumn('produks', 'harga_pokok')) {
                $table->decimal('harga_pokok', 15, 2)->default(0)->nullable()->after('harga_jual')
                    ->comment('Harga Pokok Produksi (BBB + BTKL + BOP)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (Schema::hasColumn('produks', 'harga_pokok')) {
                $table->dropColumn('harga_pokok');
            }
        });
    }
};
