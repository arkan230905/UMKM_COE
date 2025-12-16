<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('asset_depreciations')) {
            Schema::create('asset_depreciations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('asset_id');
                $table->unsignedInteger('tahun');
                $table->decimal('beban_penyusutan', 15, 2)->default(0);
                $table->decimal('akumulasi_penyusutan', 15, 2)->default(0);
                $table->decimal('nilai_buku_akhir', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('asset_id')
                    ->references('id')
                    ->on('asets')
                    ->onDelete('cascade');

                $table->unique(['asset_id', 'tahun']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
    }
};
