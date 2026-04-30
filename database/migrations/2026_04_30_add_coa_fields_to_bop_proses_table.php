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
            // COA untuk jurnal produksi BOP
            $table->string('coa_debit_id', 20)->nullable()->after('bop_per_unit')->comment('COA Debit untuk BOP (BDP-BOP)');
            $table->string('coa_kredit_id', 20)->nullable()->after('coa_debit_id')->comment('COA Kredit untuk BOP (Hutang/Persediaan)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->dropColumn(['coa_debit_id', 'coa_kredit_id']);
        });
    }
};
