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
        if (Schema::hasTable('bop_proses') && Schema::hasColumn('bop_proses', 'produk_id')) {
            Schema::table('bop_proses', function (Blueprint $table) {
                $table->foreignId('produk_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->foreignId('produk_id')->nullable(false)->change();
        });
    }
};
