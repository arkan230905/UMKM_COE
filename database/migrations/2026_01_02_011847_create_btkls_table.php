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
        Schema::create('btkls', function (Blueprint $table) {
            $table->id();
            $table->string('kode_proses')->unique();
            $table->foreignId('jabatan_id')->constrained('jabatans')->onDelete('cascade'); // Reference to Jabatan with category 'btkl'
            $table->decimal('tarif_per_jam', 15, 2)->default(0); // Tarif BTKL per Jam (Rp/jam)
            $table->enum('satuan', ['Jam', 'Unit', 'Batch'])->default('Jam'); // Satuan (Jam/Unit/Batch)
            $table->integer('kapasitas_per_jam')->default(0); // Kapasitas per Jam (berapa pcs bisa diproduksi per jam)
            $table->text('deskripsi_proses')->nullable(); // Deskripsi proses
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['jabatan_id', 'is_active']);
            $table->index(['kode_proses', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('btkls');
    }
};
