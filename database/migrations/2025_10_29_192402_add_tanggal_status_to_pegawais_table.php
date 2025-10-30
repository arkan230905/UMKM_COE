<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambahkan kolom dengan nilai default
        Schema::table('pegawais', function (Blueprint $table) {
            $table->date('tanggal_masuk')->after('jabatan')->default(now()->toDateString());
            $table->date('tanggal_keluar')->nullable()->after('tanggal_masuk');
            $table->boolean('status_aktif')->default(true)->after('tanggal_keluar');
            
            // Tambahkan kolom created_by dan updated_by
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        // Update data yang sudah ada
        DB::table('pegawais')->update([
            'tanggal_masuk' => now()->toDateString(),
            'status_aktif' => true,
            'created_by' => 1, // Asumsikan user dengan ID 1 adalah admin
            'updated_by' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            $table->dropColumn([
                'tanggal_masuk',
                'tanggal_keluar',
                'status_aktif',
                'created_by',
                'updated_by'
            ]);
        });
    }
};
