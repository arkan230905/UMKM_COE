<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom yang hilang di tabel coas
     */
    public function up(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            if (!Schema::hasColumn('coas', 'saldo_awal')) {
                $table->decimal('saldo_awal', 15, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('coas', 'posted_saldo_awal')) {
                $table->boolean('posted_saldo_awal')->default(false)->nullable();
            }
            if (!Schema::hasColumn('coas', 'tanggal_saldo_awal')) {
                $table->date('tanggal_saldo_awal')->nullable();
            }
            if (!Schema::hasColumn('coas', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            $columns = ['saldo_awal', 'posted_saldo_awal', 'tanggal_saldo_awal', 'keterangan'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('coas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
