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
        Schema::table('asets', function (Blueprint $table) {
            // Tambah jenis_perolehan: 'pembelian_baru' atau 'saldo_awal'
            if (!Schema::hasColumn('asets', 'jenis_perolehan')) {
                // Check apakah jenis_aset column ada (reference point), kalau tidak add after kategori_aset_id
                $afterColumn = Schema::hasColumn('asets', 'jenis_aset') ? 'jenis_aset' : 'kategori_aset_id';
                
                $table->enum('jenis_perolehan', ['pembelian_baru', 'saldo_awal'])
                      ->default('pembelian_baru')
                      ->after($afterColumn)
                      ->comment('Jenis perolehan aset: pembelian baru atau saldo awal');
            }
            
            // Tambah sumber_dana_coa_id untuk kredit entry (kas/bank/utang/modal)
            if (!Schema::hasColumn('asets', 'sumber_dana_coa_id')) {
                $table->unsignedBigInteger('sumber_dana_coa_id')
                      ->nullable()
                      ->after('expense_coa_id')
                      ->comment('COA sumber dana untuk perolehan aset (Kas/Bank/Utang/Modal)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'jenis_perolehan')) {
                $table->dropColumn('jenis_perolehan');
            }
            if (Schema::hasColumn('asets', 'sumber_dana_coa_id')) {
                $table->dropColumn('sumber_dana_coa_id');
            }
        });
    }
};
