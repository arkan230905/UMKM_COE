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
        Schema::table('beban_operasional', function (Blueprint $table) {
            // Hapus field transaksi
            $table->dropColumn(['tanggal', 'periode', 'nominal']);
            
            // Rename nominal untuk master (opsional - budget/default)
            $table->decimal('budget_nominal', 15, 2)->nullable()->after('keterangan');
            
            // Tambah field master
            $table->string('kode')->unique()->after('id'); // Kode unik master
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('budget_nominal');
            $table->foreignId('default_coa_id')->nullable()->after('status'); // COA default (tanpa constraint dulu)
            
            // Update indexes
            $table->dropIndex(['tanggal', 'periode']);
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
        Schema::table('beban_operasional', function (Blueprint $table) {
            // Kembalikan field transaksi
            $table->date('tanggal')->after('id');
            $table->string('periode', 20)->after('tanggal');
            $table->decimal('nominal', 15, 2)->after('periode');
            
            // Hapus field master
            $table->dropColumn(['kode', 'budget_nominal', 'status', 'default_coa_id']);
            
            // Restore indexes
            $table->index(['tanggal', 'periode']);
            $table->dropIndex('status');
            $table->dropIndex('kode');
        });
    }
};
