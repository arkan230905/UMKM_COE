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
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->decimal('sub_satuan_1_nilai', 15, 4)->nullable()->after('sub_satuan_1_konversi');
            $table->decimal('sub_satuan_2_nilai', 15, 4)->nullable()->after('sub_satuan_2_konversi');
            $table->decimal('sub_satuan_3_nilai', 15, 4)->nullable()->after('sub_satuan_3_konversi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropColumn(['sub_satuan_1_nilai', 'sub_satuan_2_nilai', 'sub_satuan_3_nilai']);
        });
    }
};
