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
            if (!Schema::hasColumn('bahan_bakus', 'satuan_id')) {
                $table->unsignedBigInteger('satuan_id')->nullable()->after('nama_bahan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            if (Schema::hasColumn('bahan_bakus', 'satuan_id')) {
                $table->dropColumn('satuan_id');
            }
        });
    }
};
