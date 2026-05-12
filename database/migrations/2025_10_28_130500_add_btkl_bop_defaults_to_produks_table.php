<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (!Schema::hasColumn('produks', 'btkl_default')) {
                $table->decimal('btkl_default', 15, 2)->nullable()->default(0)->after('harga_jual');
            }
            if (!Schema::hasColumn('produks', 'bop_default')) {
                $table->decimal('bop_default', 15, 2)->nullable()->default(0)->after('btkl_default');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            if (Schema::hasColumn('produks', 'btkl_default')) {
                $table->dropColumn('btkl_default');
            }
            if (Schema::hasColumn('produks', 'bop_default')) {
                $table->dropColumn('bop_default');
            }
        });
    }
};
