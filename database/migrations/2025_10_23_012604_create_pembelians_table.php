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
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            
            // Kolom untuk Multi-tenant (Owner)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->date('tanggal');
            $table->string('nomor_faktur')->nullable(); // Ditambahkan untuk nomor referensi
            $table->string('bukti_faktur')->nullable(); // Kolom yang tadinya menyebabkan error duplikat
            
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();

            // Indexing untuk performa query per owner
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};