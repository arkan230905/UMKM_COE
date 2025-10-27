<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bops', function (Blueprint $table) {
            // Menambah kolom nominal & tanggal setelah keterangan
            $table->decimal('nominal', 15, 2)->nullable()->after('keterangan');
            $table->date('tanggal')->nullable()->after('nominal');
        });
    }

    public function down(): void
    {
        Schema::table('bops', function (Blueprint $table) {
            // Hapus kolom jika rollback
            $table->dropColumn(['nominal', 'tanggal']);
        });
    }
};
