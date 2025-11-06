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
        if (!Schema::hasTable('asets')) {
            Schema::create('asets', function (Blueprint $table) {
                $table->id();
                $table->string('kode_aset')->unique();
                $table->string('nama_aset');
                $table->string('kategori');
                $table->foreignId('coa_id')->nullable()->constrained('coas')->onDelete('set null');
                $table->date('tanggal_perolehan');
                $table->decimal('harga_perolehan', 15, 2);
                $table->decimal('nilai_sisa', 15, 2);
                $table->integer('umur_ekonomis_tahun');
                $table->enum('metode_penyusutan', ['garis_lurus', 'saldo_menurun', 'sum_of_years_digits'])->default('garis_lurus');
                $table->decimal('persentase_penyusutan', 5, 2)->nullable();
                $table->string('lokasi')->nullable();
                $table->string('nomor_serial')->nullable();
                $table->enum('status', ['aktif', 'tidak_aktif', 'dihapus'])->default('aktif');
                $table->text('keterangan')->nullable();
                $table->decimal('nilai_buku', 15, 2)->default(0);
                $table->decimal('akumulasi_penyusutan', 15, 2)->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asets');
    }
};
