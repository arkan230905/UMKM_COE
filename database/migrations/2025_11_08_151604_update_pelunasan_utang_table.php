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
        Schema::table('pelunasan_utang', function (Blueprint $table) {
            if (!Schema::hasColumn('pelunasan_utang', 'kode_transaksi')) {
                $table->string('kode_transaksi')->unique()->after('id');
            }
            
            if (!Schema::hasColumn('pelunasan_utang', 'akun_kas_id')) {
                $table->foreignId('akun_kas_id')->after('pembelian_id')->constrained('coas');
            }
            
            if (!Schema::hasColumn('pelunasan_utang', 'status')) {
                $table->string('status')->default('lunas')->after('keterangan');
            }
            
            if (!Schema::hasColumn('pelunasan_utang', 'user_id')) {
                $table->foreignId('user_id')->after('status')->constrained('users');
            }
            
            if (!Schema::hasColumn('pelunasan_utang', 'catatan')) {
                $table->text('catatan')->nullable()->after('user_id');
            }
            
            if (!Schema::hasColumn('pelunasan_utang', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelunasan_utang', function (Blueprint $table) {
            //
        });
    }
};
