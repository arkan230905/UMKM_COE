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
        Schema::table('boms', function (Blueprint $table) {
            $table->integer('qty')->after('bahan_baku_id');
            $table->string('satuan', 20)->after('qty');
            $table->decimal('harga_satuan', 15, 2)->after('satuan');
            $table->decimal('total_harga', 15, 2)->after('harga_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            $table->dropColumn(['qty', 'satuan', 'harga_satuan', 'total_harga']);
        });
    }
};
