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
        if (!Schema::hasTable('journal_lines')) {
            Schema::create('journal_lines', function (Blueprint $table) {
                $table->id();
                
                // Relasi ke Header Jurnal (merujuk ke journal_entries)
                $table->unsignedBigInteger('journal_entry_id');
                $table->foreign('journal_entry_id')
                      ->references('id')
                      ->on('journal_entries')
                      ->onDelete('cascade');
                
                // Relasi ke Akun (merujuk ke coas)
                $table->unsignedBigInteger('coa_id');
                $table->foreign('coa_id')
                      ->references('id')
                      ->on('coas')
                      ->onDelete('cascade');
                
                // Nominal & Keterangan
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->string('keterangan')->nullable();
                
                $table->timestamps();

                // Index untuk performa laporan keuangan
                $table->index('journal_entry_id');
                $table->index('coa_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};