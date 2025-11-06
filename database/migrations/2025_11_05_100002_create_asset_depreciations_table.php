<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->unsignedInteger('tahun');
            $table->decimal('beban_penyusutan', 15, 2);
            $table->decimal('akumulasi_penyusutan', 15, 2);
            $table->decimal('nilai_buku_akhir', 15, 2);
            $table->timestamps();
            $table->unique(['asset_id','tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
    }
};
