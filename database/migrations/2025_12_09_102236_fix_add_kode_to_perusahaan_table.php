<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key jika sudah ada (untuk menghindari error)
            if (Schema::hasColumn('users', 'perusahaan_id')) {
                $table->dropForeign(['perusahaan_id']);
                $table->dropColumn('perusahaan_id');
            }
            
            // Tambahkan kolom perusahaan_id
            $table->foreignId('perusahaan_id')
                  ->nullable()
                  ->after('id')  // Ubah dari 'role' ke 'id'
                  ->constrained('perusahaan')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
            $table->dropColumn('perusahaan_id');
        });
    }
};