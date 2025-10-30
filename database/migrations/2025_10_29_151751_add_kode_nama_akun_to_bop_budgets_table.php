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
        Schema::table('bop_budgets', function (Blueprint $table) {
            $table->string('kode_akun', 20)->after('coa_id');
            $table->string('nama_akun', 100)->after('kode_akun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_budgets', function (Blueprint $table) {
            $table->dropColumn(['kode_akun', 'nama_akun']);
        });
    }
};
