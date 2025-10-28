<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            if (!Schema::hasColumn('bahan_bakus', 'stok')) {
                $table->decimal('stok', 15, 4)->default(0)->after('satuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            if (Schema::hasColumn('bahan_bakus', 'stok')) {
                $table->dropColumn('stok');
            }
        });
    }
};
