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
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            // Sub Satuan 1
            $table->unsignedBigInteger('sub_satuan_1_id')->nullable()->after('satuan_id');
            $table->decimal('sub_satuan_1_konversi', 15, 4)->nullable()->after('sub_satuan_1_id');
            
            // Sub Satuan 2
            $table->unsignedBigInteger('sub_satuan_2_id')->nullable()->after('sub_satuan_1_konversi');
            $table->decimal('sub_satuan_2_konversi', 15, 4)->nullable()->after('sub_satuan_2_id');
            
            // Sub Satuan 3
            $table->unsignedBigInteger('sub_satuan_3_id')->nullable()->after('sub_satuan_2_konversi');
            $table->decimal('sub_satuan_3_konversi', 15, 4)->nullable()->after('sub_satuan_3_id');
            
            // Foreign keys
            $table->foreign('sub_satuan_1_id')->references('id')->on('satuans')->onDelete('set null');
            $table->foreign('sub_satuan_2_id')->references('id')->on('satuans')->onDelete('set null');
            $table->foreign('sub_satuan_3_id')->references('id')->on('satuans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_pendukungs', function (Blueprint $table) {
            $table->dropForeign(['sub_satuan_1_id']);
            $table->dropForeign(['sub_satuan_2_id']);
            $table->dropForeign(['sub_satuan_3_id']);
            
            $table->dropColumn([
                'sub_satuan_1_id',
                'sub_satuan_1_konversi',
                'sub_satuan_2_id',
                'sub_satuan_2_konversi',
                'sub_satuan_3_id',
                'sub_satuan_3_konversi'
            ]);
        });
    }
};
