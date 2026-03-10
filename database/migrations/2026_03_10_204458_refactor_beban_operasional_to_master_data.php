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
            // Hapus field transaksi jika ada
            if (Schema::hasColumn('beban_operasional', 'tanggal')) {
                $table->dropColumn('tanggal');
            }
            if (Schema::hasColumn('beban_operasional', 'periode')) {
                $table->dropColumn('periode');
            }
            if (Schema::hasColumn('beban_operasional', 'nominal')) {
                $table->dropColumn('nominal');
            }
            
            // Tambah field master jika belum ada
            if (!Schema::hasColumn('beban_operasional', 'kode')) {
                $table->string('kode')->unique()->after('id'); // Kode unik master
            }
            if (!Schema::hasColumn('beban_operasional', 'budget_bulanan')) {
                $table->decimal('budget_bulanan', 15, 2)->nullable()->after('keterangan');
            }
            if (!Schema::hasColumn('beban_operasional', 'status')) {
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('budget_bulanan');
            }
            
            // Update indexes jika belum ada
            if (!Schema::hasIndex('beban_operasional', 'beban_operasional_kategori_index')) {
                $table->index('kategori');
            }
            if (!Schema::hasIndex('beban_operasional', 'beban_operasional_status_index')) {
                $table->index('status');
            }
            if (Schema::hasColumn('beban_operasional', 'kode') && !Schema::hasIndex('beban_operasional', 'beban_operasional_kode_index')) {
                $table->index('kode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beban_operasional', function (Blueprint $table) {
            // Kembalikan field transaksi jika belum ada
            if (!Schema::hasColumn('beban_operasional', 'tanggal')) {
                $table->date('tanggal')->after('id');
            }
            if (!Schema::hasColumn('beban_operasional', 'periode')) {
                $table->string('periode', 20)->after('tanggal');
            }
            if (!Schema::hasColumn('beban_operasional', 'nominal')) {
                $table->decimal('nominal', 15, 2)->after('periode');
            }
            
            // Hapus field master jika ada
            if (Schema::hasColumn('beban_operasional', 'kode')) {
                $table->dropColumn('kode');
            }
            if (Schema::hasColumn('beban_operasional', 'budget_bulanan')) {
                $table->dropColumn('budget_bulanan');
            }
            if (Schema::hasColumn('beban_operasional', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('beban_operasional', 'default_coa_id')) {
                $table->dropColumn('default_coa_id');
            }
        });
    }
};
