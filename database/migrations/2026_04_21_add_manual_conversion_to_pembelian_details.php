<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian_details', function (Blueprint $table) {
            // Add columns for manual conversion data
            $table->unsignedBigInteger('sub_satuan_id')->nullable()->after('jumlah_satuan_utama');
            $table->string('sub_satuan_nama')->nullable()->after('sub_satuan_id');
            $table->decimal('manual_conversion_factor', 10, 4)->nullable()->after('sub_satuan_nama');
            $table->decimal('jumlah_sub_satuan', 15, 4)->nullable()->after('manual_conversion_factor');
            $table->json('manual_conversion_data')->nullable()->after('jumlah_sub_satuan');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_details', function (Blueprint $table) {
            $table->dropColumn([
                'sub_satuan_id',
                'sub_satuan_nama',
                'manual_conversion_factor',
                'jumlah_sub_satuan',
                'manual_conversion_data'
            ]);
        });
    }
};
