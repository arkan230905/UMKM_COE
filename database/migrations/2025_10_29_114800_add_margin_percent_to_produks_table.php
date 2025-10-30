<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (!Schema::hasColumn('produks', 'margin_percent')) {
                $table->decimal('margin_percent', 5, 2)->default(30.00)->after('harga_jual'); // 30% default
            }
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (Schema::hasColumn('produks', 'margin_percent')) {
                $table->dropColumn('margin_percent');
            }
        });
    }
};
