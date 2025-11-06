<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            if (!Schema::hasColumn('jabatans', 'kategori')) {
                $table->enum('kategori', ['btkl','btktl'])->default('btkl')->after('nama');
            }
            if (!Schema::hasColumn('jabatans', 'tunjangan')) {
                $table->decimal('tunjangan', 15, 2)->default(0)->after('tarif');
            }
            if (!Schema::hasColumn('jabatans', 'gaji')) {
                $table->decimal('gaji', 15, 2)->default(0)->after('tunjangan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            foreach (['kategori','tunjangan','gaji'] as $col) {
                if (Schema::hasColumn('jabatans', $col)) {
                    try { $table->dropColumn($col); } catch (\Throwable $e) {}
                }
            }
        });
    }
};
