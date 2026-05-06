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
        Schema::table('bop_proses', function (Blueprint $table) {
            // Add keterangan column if it doesn't exist
            if (!Schema::hasColumn('bop_proses', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('bop_per_unit');
                echo "Added keterangan to bop_proses table\n";
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            if (Schema::hasColumn('bop_proses', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });
    }
};
