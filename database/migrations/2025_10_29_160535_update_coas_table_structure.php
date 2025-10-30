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
        Schema::table('coas', function (Blueprint $table) {
            // Change existing columns if needed
            $table->string('kode_akun', 10)->change();
            
            // Add new columns
            $table->string('kategori_akun', 50)->after('tipe_akun');
            $table->boolean('is_akun_header')->default(false)->after('kategori_akun');
            $table->string('kode_induk', 10)->nullable()->after('is_akun_header');
            $table->enum('saldo_normal', ['debit', 'kredit'])->after('kode_induk');
            $table->text('keterangan')->nullable()->after('saldo_normal');
            
            // Add foreign key constraint for kode_induk
            $table->foreign('kode_induk')
                  ->references('kode_akun')
                  ->on('coas')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['kode_induk']);
            
            // Drop columns
            $table->dropColumn([
                'kategori_akun',
                'is_akun_header',
                'kode_induk',
                'saldo_normal',
                'keterangan'
            ]);
            
            // Revert kode_akun to string without length
            $table->string('kode_akun')->change();
        });
    }
};
