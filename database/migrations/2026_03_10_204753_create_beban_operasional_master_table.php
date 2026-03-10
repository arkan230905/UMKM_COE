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
            $table->string('kode')->unique(); // Kode unik master (BO001, BO002, dst)
            $table->string('kategori', 50); // Administrasi, Marketing, Utilitas, dll
            $table->string('nama_beban'); // Nama jenis beban
            $table->text('keterangan')->nullable(); // Keterangan master
            $table->decimal('budget_nominal', 15, 2)->nullable(); // Budget/default nominal (opsional)
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif'); // Status master
            $table->foreignId('default_coa_id')->nullable(); // COA default untuk transaksi (opsional)
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Indexes untuk performance
            $table->index('kategori');
            $table->index('status');
            $table->index('kode');
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
