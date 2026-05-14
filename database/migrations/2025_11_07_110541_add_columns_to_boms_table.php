<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom detail biaya ke tabel boms
     */
    public function up(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            // Menghapus 'after' untuk menghindari error "Column not found"
            $table->integer('qty')->default(0);
            $table->string('satuan', 20)->nullable();
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('total_harga', 15, 2)->default(0);
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