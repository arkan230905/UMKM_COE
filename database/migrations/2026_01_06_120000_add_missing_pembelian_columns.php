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
            if (!Schema::hasColumn('pembelians', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'credit', 'transfer'])->default('cash')->after('total');
            }
            if (!Schema::hasColumn('pembelians', 'total_harga')) {
                $table->decimal('total_harga', 15, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('pembelians', 'terbayar')) {
                $table->decimal('terbayar', 15, 2)->default(0)->after('total_harga');
            }
            if (!Schema::hasColumn('pembelians', 'sisa_pembayaran')) {
                $table->decimal('sisa_pembayaran', 15, 2)->default(0)->after('terbayar');
            }
            if (!Schema::hasColumn('pembelians', 'status')) {
                $table->enum('status', ['lunas', 'belum_lunas'])->default('belum_lunas')->after('sisa_pembayaran');
            }
            if (!Schema::hasColumn('pembelians', 'nomor_pembelian')) {
                $table->string('nomor_pembelian')->nullable()->after('id');
            }
            if (!Schema::hasColumn('pembelians', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $columns = ['payment_method', 'total_harga', 'terbayar', 'sisa_pembayaran', 'status', 'nomor_pembelian', 'keterangan'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('pembelians', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
