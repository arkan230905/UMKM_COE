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
        // 🔒 SECURITY: Add user_id columns to all tables that need multi-tenant isolation
        
        // kategori_asets
        if (Schema::hasTable('kategori_asets') && !Schema::hasColumn('kategori_asets', 'user_id')) {
            Schema::table('kategori_asets', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }
        
        // jenis_asets
        if (Schema::hasTable('jenis_asets') && !Schema::hasColumn('jenis_asets', 'user_id')) {
            Schema::table('jenis_asets', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }
        
        // jabatans
        if (Schema::hasTable('jabatans') && !Schema::hasColumn('jabatans', 'user_id')) {
            Schema::table('jabatans', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }
        
        // kualifikasi_tenaga_kerjas
        if (Schema::hasTable('kualifikasi_tenaga_kerjas') && !Schema::hasColumn('kualifikasi_tenaga_kerjas', 'user_id')) {
            Schema::table('kualifikasi_tenaga_kerjas', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }
        
        // beban_operasionals
        if (Schema::hasTable('beban_operasionals') && !Schema::hasColumn('beban_operasionals', 'user_id')) {
            Schema::table('beban_operasionals', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }
        
        // bop_lainnyas
        if (Schema::hasTable('bop_lainnyas') && !Schema::hasColumn('bop_lainnyas', 'user_id')) {
            Schema::table('bop_lainnyas', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }
        
        // pelunasan_utangs
        if (Schema::hasTable('pelunasan_utangs') && !Schema::hasColumn('pelunasan_utangs', 'user_id')) {
            Schema::table('pelunasan_utangs', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }
        
        // pembayaran_bebans
        if (Schema::hasTable('pembayaran_bebans') && !Schema::hasColumn('pembayaran_bebans', 'user_id')) {
            Schema::table('pembayaran_bebans', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'kategori_asets',
            'jenis_asets', 
            'jabatans',
            'kualifikasi_tenaga_kerjas',
            'beban_operasionals',
            'bop_lainnyas',
            'pelunasan_utangs',
            'pembayaran_bebans'
        ];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                });
            }
        }
    }
};
