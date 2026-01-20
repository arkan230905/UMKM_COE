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
        // Update BOP Lainnya table
        Schema::table('bop_lainnyas', function (Blueprint $table) {
            // Rename jumlah to budget for consistency
            if (Schema::hasColumn('bop_lainnyas', 'jumlah')) {
                $table->renameColumn('jumlah', 'budget');
            }
            
            // Add missing fields
            if (!Schema::hasColumn('bop_lainnyas', 'kuantitas_per_jam')) {
                $table->integer('kuantitas_per_jam')->default(1)->after('budget');
            }
            if (!Schema::hasColumn('bop_lainnyas', 'aktual')) {
                $table->decimal('aktual', 15, 2)->default(0)->after('kuantitas_per_jam');
            }
        });

        // Update BOP Proses table
        Schema::table('bop_proses', function (Blueprint $table) {
            // Add budget and aktual fields
            if (!Schema::hasColumn('bop_proses', 'budget')) {
                $table->decimal('budget', 15, 2)->default(0)->after('total_bop_per_jam');
            }
            if (!Schema::hasColumn('bop_proses', 'aktual')) {
                $table->decimal('aktual', 15, 2)->default(0)->after('budget');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert BOP Lainnya table
        Schema::table('bop_lainnyas', function (Blueprint $table) {
            if (Schema::hasColumn('bop_lainnyas', 'budget')) {
                $table->renameColumn('budget', 'jumlah');
            }
            if (Schema::hasColumn('bop_lainnyas', 'kuantitas_per_jam')) {
                $table->dropColumn('kuantitas_per_jam');
            }
            if (Schema::hasColumn('bop_lainnyas', 'aktual')) {
                $table->dropColumn('aktual');
            }
        });

        // Revert BOP Proses table
        Schema::table('bop_proses', function (Blueprint $table) {
            if (Schema::hasColumn('bop_proses', 'budget')) {
                $table->dropColumn('budget');
            }
            if (Schema::hasColumn('bop_proses', 'aktual')) {
                $table->dropColumn('aktual');
            }
        });
    }
};