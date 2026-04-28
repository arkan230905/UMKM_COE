<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bukti_pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('bukti_pembayaran', 'penjualan_id')) {
                $table->unsignedBigInteger('penjualan_id')->after('id');
            }
            if (!Schema::hasColumn('bukti_pembayaran', 'file_path')) {
                $table->string('file_path')->after('penjualan_id');
            }
            if (!Schema::hasColumn('bukti_pembayaran', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('file_path');
            }
        });

        // Add foreign key only if not exists
        try {
            Schema::table('bukti_pembayaran', function (Blueprint $table) {
                $table->foreign('penjualan_id')
                      ->references('id')->on('penjualans')
                      ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key already exists, skip
        }
    }

    public function down(): void
    {
        Schema::table('bukti_pembayaran', function (Blueprint $table) {
            $table->dropForeign(['penjualan_id']);
            $table->dropColumn(['penjualan_id', 'file_path', 'keterangan']);
        });
    }
};
