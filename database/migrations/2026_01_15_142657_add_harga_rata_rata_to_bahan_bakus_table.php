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
            $table->decimal('harga_rata_rata', 10, 2)->nullable()->after('harga_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropColumn('harga_rata_rata');
        });
    }
};
