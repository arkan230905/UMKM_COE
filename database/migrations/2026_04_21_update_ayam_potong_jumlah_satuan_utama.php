<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update Ayam Potong purchase detail to 40 Kilogram
        DB::table('pembelian_details')
            ->where('bahan_baku_id', 1)
            ->update(['jumlah_satuan_utama' => 40]);
        
        // Also update the stock movement
        DB::table('stock_movements')
            ->where('ref_type', 'purchase')
            ->where('item_id', 1)
            ->where('item_type', 'material')
            ->update([
                'qty' => 40,
                'total_cost' => DB::raw('40 * unit_cost')
            ]);
    }

    public function down(): void
    {
        // Revert changes
        DB::table('pembelian_details')
            ->where('bahan_baku_id', 1)
            ->update(['jumlah_satuan_utama' => 50]);
        
        DB::table('stock_movements')
            ->where('ref_type', 'purchase')
            ->where('item_id', 1)
            ->where('item_type', 'material')
            ->update([
                'qty' => 50,
                'total_cost' => DB::raw('50 * unit_cost')
            ]);
    }
};
