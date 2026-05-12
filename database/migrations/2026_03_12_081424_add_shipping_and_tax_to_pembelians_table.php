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
        Schema::table('pembelians', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->default(0)->after('total_harga')->comment('Subtotal sebelum biaya kirim dan PPN');
            $table->decimal('biaya_kirim', 15, 2)->default(0)->after('subtotal')->comment('Biaya pengiriman');
            $table->decimal('ppn_persen', 5, 2)->default(0)->after('biaya_kirim')->comment('Persentase PPN (misal: 11.00 untuk 11%)');
            $table->decimal('ppn_nominal', 15, 2)->default(0)->after('ppn_persen')->comment('Nominal PPN dalam rupiah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'biaya_kirim', 'ppn_persen', 'ppn_nominal']);
        });
    }
};
