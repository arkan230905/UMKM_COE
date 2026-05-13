<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('btkls', function (Blueprint $table) {
            $table->id();
            // Tambahkan user_id
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('kode_proses'); 
            $table->foreignId('jabatan_id')->constrained('jabatans')->onDelete('cascade');
            $table->decimal('tarif_per_jam', 15, 2)->default(0);
            $table->enum('satuan', ['Jam', 'Unit', 'Batch'])->default('Jam');
            $table->integer('kapasitas_per_jam')->default(0);
            $table->text('deskripsi_proses')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index(['jabatan_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('btkls');
    }
};