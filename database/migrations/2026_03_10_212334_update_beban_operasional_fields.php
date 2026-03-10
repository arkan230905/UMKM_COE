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
        Schema::table('beban_operasional', function (Blueprint $table) {
            // Rename budget_nominal to budget_bulanan
            $table->renameColumn('budget_nominal', 'budget_bulanan');
            
            // Rename default_coa_id to akun_beban_id
            $table->renameColumn('default_coa_id', 'akun_beban_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beban_operasional', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('budget_bulanan', 'budget_nominal');
            $table->renameColumn('akun_beban_id', 'default_coa_id');
        });
    }
};
