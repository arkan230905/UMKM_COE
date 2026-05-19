<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get existing columns
        $columns = Schema::getColumnListing('pembelians');
        
        Schema::table('pembelians', function (Blueprint $table) use ($columns) {
            // Add columns only if they don't exist
            if (!in_array('subtotal', $columns)) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('tanggal');
            }
            
            if (!in_array('biaya_kirim', $columns)) {
                $table->decimal('biaya_kirim', 15, 2)->default(0)->after('subtotal');
            }
            
            if (!in_array('ppn_persen', $columns)) {
                $table->decimal('ppn_persen', 5, 2)->default(0)->after('biaya_kirim');
            }
            
            if (!in_array('ppn_nominal', $columns)) {
                $table->decimal('ppn_nominal', 15, 2)->default(0)->after('ppn_persen');
            }
            
            if (!in_array('total_harga', $columns)) {
                $table->decimal('total_harga', 15, 2)->default(0)->after('ppn_nominal');
            }
            
            if (!in_array('terbayar', $columns)) {
                $table->decimal('terbayar', 15, 2)->default(0)->after('total_harga');
            }
            
            if (!in_array('sisa_pembayaran', $columns)) {
                $table->decimal('sisa_pembayaran', 15, 2)->default(0)->after('terbayar');
            }
            
            if (!in_array('status', $columns)) {
                $table->string('status', 50)->default('belum_lunas')->after('sisa_pembayaran');
            }
            
            if (!in_array('keterangan', $columns)) {
                $table->text('keterangan')->nullable()->after('bank_id');
            }
        });
        
        // Add index for nomor_pembelian if it doesn't exist
        if (in_array('nomor_pembelian', $columns)) {
            try {
                DB::statement('CREATE INDEX pembelians_nomor_pembelian_index ON pembelians (nomor_pembelian)');
            } catch (\Exception $e) {
                // Index might already exist, ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $columns = ['subtotal', 'biaya_kirim', 'ppn_persen', 'ppn_nominal', 
                       'total_harga', 'terbayar', 'sisa_pembayaran', 'status', 'keterangan'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('pembelians', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
