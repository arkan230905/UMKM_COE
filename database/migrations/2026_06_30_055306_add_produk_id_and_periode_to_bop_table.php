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
        Schema::table('bop', function (Blueprint $table) {
            // Add produk_id to link BOP with specific product
            $table->unsignedBigInteger('produk_id')->nullable()->after('user_id');
            $table->foreign('produk_id')->references('id')->on('produks')->onDelete('cascade');
            
            // Add periode (YYYY-MM) to track monthly BOP
            $table->string('periode', 7)->nullable()->after('produk_id')->comment('Format: YYYY-MM');
            
            // Add jumlah_produksi from target produksi
            $table->decimal('jumlah_produksi', 15, 2)->default(0)->after('periode')->comment('Target produksi bulan ini');
            
            // Add index for better query performance
            $table->index(['user_id', 'produk_id', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop', function (Blueprint $table) {
            $table->dropForeign(['produk_id']);
            $table->dropIndex(['user_id', 'produk_id', 'periode']);
            $table->dropColumn(['produk_id', 'periode', 'jumlah_produksi']);
        });
    }
};
