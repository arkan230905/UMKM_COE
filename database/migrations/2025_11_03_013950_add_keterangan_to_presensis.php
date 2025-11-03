<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('presensis')) {
            Schema::table('presensis', function (Blueprint $table) {
                if (!Schema::hasColumn('presensis', 'keterangan')) {
                    $table->string('keterangan', 255)->nullable()->after('jumlah_jam');
                }
                if (!Schema::hasColumn('presensis', 'jumlah_jam')) {
                    $table->decimal('jumlah_jam', 8, 2)->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('presensis')) {
            Schema::table('presensis', function (Blueprint $table) {
                if (Schema::hasColumn('presensis', 'keterangan')) {
                    try { $table->dropColumn('keterangan'); } catch (\Throwable $e) {}
                }
            });
        }
    }
};
