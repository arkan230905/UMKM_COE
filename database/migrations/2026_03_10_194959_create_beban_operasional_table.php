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
        Schema::create('beban_operasional', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('periode', 20);
            $table->string('kategori', 50);
            $table->string('nama_beban');
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['tanggal', 'periode']);
            $table->index('kategori');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beban_operasional');
    }
};
