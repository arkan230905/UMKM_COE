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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'latitude')) $table->string('latitude')->nullable()->after('alamat_pengiriman');
            if (!Schema::hasColumn('orders', 'longitude')) $table->string('longitude')->nullable()->after('latitude');
            if (!Schema::hasColumn('orders', 'detail_alamat')) $table->text('detail_alamat')->nullable()->after('longitude');
            if (!Schema::hasColumn('orders', 'kecamatan')) $table->string('kecamatan')->nullable()->after('detail_alamat');
            if (!Schema::hasColumn('orders', 'kota')) $table->string('kota')->nullable()->after('kecamatan');
            if (!Schema::hasColumn('orders', 'kode_pos')) $table->string('kode_pos')->nullable()->after('kota');
            
            if (!Schema::hasColumn('orders', 'subtotal_amount')) $table->decimal('subtotal_amount', 15, 2)->default(0)->after('total_amount');
            if (!Schema::hasColumn('orders', 'ppn_amount')) $table->decimal('ppn_amount', 15, 2)->default(0)->after('subtotal_amount');
            if (!Schema::hasColumn('orders', 'ongkir_amount')) $table->decimal('ongkir_amount', 15, 2)->default(0)->after('ppn_amount');
            
            if (!Schema::hasColumn('orders', 'bank_tujuan_transfer')) $table->string('bank_tujuan_transfer')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'detail_alamat',
                'kecamatan',
                'kota',
                'kode_pos',
                'subtotal_amount',
                'ppn_amount',
                'ongkir_amount',
                'bank_tujuan_transfer'
            ]);
        });
    }
};
