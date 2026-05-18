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
            // Add missing columns that are in the model but not in migrations
            if (!Schema::hasColumn('pembelians', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('tanggal');
            }
            
            if (!Schema::hasColumn('pembelians', 'biaya_kirim')) {
                $table->decimal('biaya_kirim', 15, 2)->default(0)->after('subtotal');
            }
            
            if (!Schema::hasColumn('pembelians', 'ppn_persen')) {
                $table->decimal('ppn_persen', 5, 2)->default(0)->after('biaya_kirim');
            }
            
            if (!Schema::hasColumn('pembelians', 'ppn_nominal')) {
                $table->decimal('ppn_nominal', 15, 2)->default(0)->after('ppn_persen');
            }
            
            if (!Schema::hasColumn('pembelians', 'total_harga')) {
                $table->decimal('total_harga', 15, 2)->default(0)->after('ppn_nominal');
            }
            
            if (!Schema::hasColumn('pembelians', 'terbayar')) {
                $table->decimal('terbayar', 15, 2)->default(0)->after('total_harga');
            }
            
            if (!Schema::hasColumn('pembelians', 'sisa_pembayaran')) {
                $table->decimal('sisa_pembayaran', 15, 2)->default(0)->after('terbayar');
            }
            
            if (!Schema::hasColumn('pembelians', 'status')) {
                $table->string('status', 50)->default('belum_lunas')->after('sisa_pembayaran');
            }
            
            if (!Schema::hasColumn('pembelians', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('payment_method');
            }
            
            if (!Schema::hasColumn('pembelians', 'nomor_pembelian')) {
                $table->string('nomor_pembelian')->nullable()->after('id');
                $table->index('nomor_pembelian');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $columns = [
                'subtotal', 'biaya_kirim', 'ppn_persen', 'ppn_nominal', 
                'total_harga', 'terbayar', 'sisa_pembayaran', 'status', 
                'keterangan', 'nomor_pembelian'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('pembelians', $column)) {
                    if ($column === 'nomor_pembelian') {
                        $table->dropIndex(['nomor_pembelian']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
