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
        Schema::table('returs', function (Blueprint $table) {
            // Tambahkan kolom tanggal jika belum ada
            if (!Schema::hasColumn('returs', 'tanggal')) {
                $table->dateTime('tanggal')->nullable()->default(now())->after('ref_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            if (Schema::hasColumn('returs', 'tanggal')) {
                $table->dropColumn('tanggal');
            }
        });
    }
};
