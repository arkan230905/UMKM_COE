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
        // First, update existing NULL values to empty string
        \DB::table('pegawais')
            ->whereNull('bank')
            ->update(['bank' => '']);
            
        \DB::table('pegawais')
            ->whereNull('nomor_rekening')
            ->update(['nomor_rekening' => '']);
            
        \DB::table('pegawais')
            ->whereNull('nama_rekening')
            ->update(['nama_rekening' => '']);
        
        // Then modify columns to be NOT NULL
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('bank', 100)->nullable(false)->default('')->change();
            $table->string('nomor_rekening', 50)->nullable(false)->default('')->change();
            $table->string('nama_rekening', 100)->nullable(false)->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('bank', 100)->nullable()->change();
            $table->string('nomor_rekening', 50)->nullable()->change();
            $table->string('nama_rekening', 100)->nullable()->change();
        });
    }
};
