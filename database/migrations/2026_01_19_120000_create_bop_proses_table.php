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
        Schema::create('bop_proses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proses_produksi_id')->constrained('proses_produksis')->onDelete('cascade');
            
            // Komponen BOP per jam (basis jam mesin)
            $table->decimal('listrik_per_jam', 15, 2)->default(0)->comment('Biaya listrik per jam mesin');
            $table->decimal('gas_bbm_per_jam', 15, 2)->default(0)->comment('Biaya gas/BBM per jam mesin');
            $table->decimal('penyusutan_mesin_per_jam', 15, 2)->default(0)->comment('Penyusutan mesin per jam');
            $table->decimal('maintenance_per_jam', 15, 2)->default(0)->comment('Biaya maintenance per jam');
            $table->decimal('gaji_mandor_per_jam', 15, 2)->default(0)->comment('Gaji mandor per jam');
            $table->decimal('lain_lain_per_jam', 15, 2)->default(0)->comment('Biaya overhead lainnya per jam');
            
            // Calculated fields
            $table->decimal('total_bop_per_jam', 15, 2)->default(0)->comment('Total BOP per jam (auto calculated)');
            $table->integer('kapasitas_per_jam')->default(0)->comment('Kapasitas per jam (sync dari BTKL)');
            $table->decimal('bop_per_unit', 15, 4)->default(0)->comment('BOP per unit produk (auto calculated)');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('proses_produksi_id');
            $table->index('is_active');
            
            // Unique constraint: satu BOP per proses
            $table->unique('proses_produksi_id', 'bop_proses_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bop_proses');
    }
};