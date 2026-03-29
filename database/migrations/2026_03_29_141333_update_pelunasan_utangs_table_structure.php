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
        Schema::table('pelunasan_utangs', function (Blueprint $table) {
            // Add missing columns
            $table->string('kode_transaksi', 50)->nullable()->after('id');
            $table->unsignedBigInteger('akun_kas_id')->nullable()->after('pembelian_id');
            $table->decimal('jumlah', 15, 2)->default(0)->after('akun_kas_id');
            
            // Add foreign key constraint
            $table->foreign('akun_kas_id')->references('id')->on('coas')->onDelete('set null');
            
            // Add index for kode_transaksi
            $table->index('kode_transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelunasan_utangs', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['akun_kas_id']);
            
            // Drop added columns
            $table->dropColumn(['kode_transaksi', 'akun_kas_id', 'jumlah']);
        });
    }
};
