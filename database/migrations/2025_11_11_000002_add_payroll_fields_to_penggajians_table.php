<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            if (!Schema::hasColumn('penggajians', 'tarif_per_jam')) {
                $table->decimal('tarif_per_jam', 12, 2)->default(0)->after('gaji_pokok');
            }
            if (!Schema::hasColumn('penggajians', 'asuransi')) {
                $table->decimal('asuransi', 12, 2)->default(0)->after('tunjangan');
            }
            if (!Schema::hasColumn('penggajians', 'bonus')) {
                $table->decimal('bonus', 12, 2)->default(0)->after('asuransi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $table->dropColumn(['tarif_per_jam', 'asuransi', 'bonus']);
        });
    }
};
