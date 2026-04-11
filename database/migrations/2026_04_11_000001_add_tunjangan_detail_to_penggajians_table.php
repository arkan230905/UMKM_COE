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
        Schema::table('penggajians', function (Blueprint $table) {
            // Add detailed tunjangan columns
            $table->decimal('tunjangan_jabatan', 15, 2)->default(0)->after('tunjangan');
            $table->decimal('tunjangan_transport', 15, 2)->default(0)->after('tunjangan_jabatan');
            $table->decimal('tunjangan_konsumsi', 15, 2)->default(0)->after('tunjangan_transport');
            $table->decimal('total_tunjangan', 15, 2)->default(0)->after('tunjangan_konsumsi');
        });

        // Migrate existing data: copy tunjangan to tunjangan_jabatan and total_tunjangan
        DB::table('penggajians')->update([
            'tunjangan_jabatan' => DB::raw('tunjangan'),
            'total_tunjangan' => DB::raw('tunjangan'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $table->dropColumn(['tunjangan_jabatan', 'tunjangan_transport', 'tunjangan_konsumsi', 'total_tunjangan']);
        });
    }
};
