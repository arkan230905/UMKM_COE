<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Tambahkan kolom tunjangan_transport jika belum ada
            if (!Schema::hasColumn('jabatans', 'tunjangan_transport')) {
                $table->decimal('tunjangan_transport', 15, 2)->default(0)->after('tunjangan');
                $table->decimal('tunjangan_konsumsi', 15, 2)->default(0)->after('tunjangan_transport');
                $table->decimal('asuransi', 15, 2)->default(0)->after('tunjangan_konsumsi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            $table->dropColumn(['tunjangan_transport', 'tunjangan_konsumsi', 'asuransi']);
        });
    }
};
