<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update tabel produksis untuk mendukung tracking proses bertahap
     */
    public function up(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            // Update status enum untuk include status baru
            $table->string('proses_saat_ini', 100)->nullable()->after('status'); // Proses yang sedang dikerjakan
            $table->integer('proses_selesai')->default(0)->after('proses_saat_ini'); // Jumlah proses yang sudah selesai
            $table->integer('total_proses')->default(0)->after('proses_selesai'); // Total proses yang harus dilalui
            
            // Waktu tracking
            $table->timestamp('waktu_mulai_produksi')->nullable()->after('total_proses');
            $table->timestamp('waktu_selesai_produksi')->nullable()->after('waktu_mulai_produksi');
        });
        
        // Update existing records to have proper status
        DB::table('produksis')->update(['status' => 'selesai']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            $table->dropColumn([
                'proses_saat_ini',
                'proses_selesai',
                'total_proses',
                'waktu_mulai_produksi',
                'waktu_selesai_produksi'
            ]);
        });
    }
};
