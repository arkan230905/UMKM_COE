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
        // Pastikan tabel pembelian_details ada dan memiliki struktur yang benar
        if (!Schema::hasTable('pembelian_details')) {
            Schema::create('pembelian_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pembelian_id')->constrained('pembelians')->onDelete('cascade');
                $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('restrict');
                $table->decimal('jumlah', 15, 2);
                $table->string('satuan', 50)->nullable();
                $table->decimal('harga_satuan', 15, 2);
                $table->decimal('subtotal', 15, 2);
                $table->decimal('faktor_konversi', 10, 4)->default(1);
                $table->timestamps();
            });
        } else {
            // Pastikan kolom-kolom yang diperlukan ada
            Schema::table('pembelian_details', function (Blueprint $table) {
                if (!Schema::hasColumn('pembelian_details', 'faktor_konversi')) {
                    $table->decimal('faktor_konversi', 10, 4)->default(1)->after('subtotal');
                }
                if (!Schema::hasColumn('pembelian_details', 'satuan')) {
                    $table->string('satuan', 50)->nullable()->after('jumlah');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu drop karena ini adalah migration untuk memastikan struktur
    }
};
