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
        Schema::table('pegawais', function (Blueprint $table) {
            // Add new columns after the existing ones, but only if they don't exist
            if (!Schema::hasColumn('pegawais', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->nullable()->after('gaji');
            }
            if (!Schema::hasColumn('pegawais', 'tarif_lembur')) {
                $table->decimal('tarif_lembur', 15, 2)->nullable()->after('tarif');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn(['gaji_pokok', 'tarif_lembur']);
        });
    }
};
