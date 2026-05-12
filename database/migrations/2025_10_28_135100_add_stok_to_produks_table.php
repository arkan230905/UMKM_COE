<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (!Schema::hasColumn('produks', 'stok')) {
                $table->decimal('stok', 15, 4)->default(0)->after('bop_default');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (Schema::hasColumn('produks', 'stok')) {
                $table->dropColumn('stok');
            }
        });
    }
};
