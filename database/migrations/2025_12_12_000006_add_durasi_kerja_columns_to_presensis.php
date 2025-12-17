<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah tabel presensis sudah ada
        if (Schema::hasTable('presensis')) {
            Schema::table('presensis', function (Blueprint $table) {
                if (!Schema::hasColumn('presensis', 'jumlah_menit_kerja')) {
                    $table->integer('jumlah_menit_kerja')->default(0)->after('status');
                }
                if (!Schema::hasColumn('presensis', 'jumlah_jam_kerja')) {
                    $table->decimal('jumlah_jam_kerja', 5, 1)->default(0)->after('jumlah_menit_kerja');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('presensis')) {
            Schema::table('presensis', function (Blueprint $table) {
                if (Schema::hasColumn('presensis', 'jumlah_menit_kerja')) {
                    $table->dropColumn('jumlah_menit_kerja');
                }
                if (Schema::hasColumn('presensis', 'jumlah_jam_kerja')) {
                    $table->dropColumn('jumlah_jam_kerja');
                }
            });
        }
    }
};
