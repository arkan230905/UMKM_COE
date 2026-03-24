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
        if (!Schema::hasTable('beban_operasional')) {
            return;
        }

        Schema::table('beban_operasional', function (Blueprint $table) {
            // Rename budget_nominal to budget_bulanan (hanya jika kolom asal ada)
            if (Schema::hasColumn('beban_operasional', 'budget_nominal') && !Schema::hasColumn('beban_operasional', 'budget_bulanan')) {
                $table->renameColumn('budget_nominal', 'budget_bulanan');
            }

            // Rename default_coa_id to akun_beban_id (hanya jika kolom asal ada)
            if (Schema::hasColumn('beban_operasional', 'default_coa_id') && !Schema::hasColumn('beban_operasional', 'akun_beban_id')) {
                $table->renameColumn('default_coa_id', 'akun_beban_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('beban_operasional')) {
            return;
        }

        Schema::table('beban_operasional', function (Blueprint $table) {
            // Reverse the changes (hanya jika kolom asal ada)
            if (Schema::hasColumn('beban_operasional', 'budget_bulanan') && !Schema::hasColumn('beban_operasional', 'budget_nominal')) {
                $table->renameColumn('budget_bulanan', 'budget_nominal');
            }
            if (Schema::hasColumn('beban_operasional', 'akun_beban_id') && !Schema::hasColumn('beban_operasional', 'default_coa_id')) {
                $table->renameColumn('akun_beban_id', 'default_coa_id');
            }
        });
    }
};
