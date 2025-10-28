<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('pegawais', 'tunjangan')) {
                $table->decimal('tunjangan', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('pegawais', 'jenis_pegawai')) {
                $table->enum('jenis_pegawai', ['btkl', 'btktl'])->default('btkl');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn(['gaji_pokok', 'tunjangan', 'jenis_pegawai']);
        });
    }
};
