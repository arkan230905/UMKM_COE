<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pembelian_details')) {
            Schema::table('pembelian_details', function (Blueprint $table) {
                if (!Schema::hasColumn('pembelian_details', 'satuan')) {
                    $table->string('satuan', 50)->nullable()->after('jumlah');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pembelian_details')) {
            Schema::table('pembelian_details', function (Blueprint $table) {
                if (Schema::hasColumn('pembelian_details', 'satuan')) {
                    $table->dropColumn('satuan');
                }
            });
        }
    }
};
