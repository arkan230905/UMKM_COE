<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Status posting JU: 'belum_posting', 'sudah_posting'
            // Hanya relevan untuk jenis_perolehan = 'pembelian_baru'
            if (!Schema::hasColumn('asets', 'status_posting_ju')) {
                $table->enum('status_posting_ju', ['belum_posting', 'sudah_posting'])
                      ->default('belum_posting')
                      ->after('is_posted')
                      ->comment('Status posting ke Jurnal Umum: belum_posting atau sudah_posting (hanya untuk pembelian_baru)');
            }
            
            // Tanggal posting ke JU
            if (!Schema::hasColumn('asets', 'tanggal_posting_ju')) {
                $table->timestamp('tanggal_posting_ju')
                      ->nullable()
                      ->after('status_posting_ju')
                      ->comment('Tanggal posting ke Jurnal Umum');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'status_posting_ju')) {
                $table->dropColumn('status_posting_ju');
            }
            if (Schema::hasColumn('asets', 'tanggal_posting_ju')) {
                $table->dropColumn('tanggal_posting_ju');
            }
        });
    }
};
