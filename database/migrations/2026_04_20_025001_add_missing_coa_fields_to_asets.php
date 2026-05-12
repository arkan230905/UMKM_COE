<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Tambahkan kolom COA jika belum ada
            if (!Schema::hasColumn('asets', 'asset_coa_id')) {
                $table->unsignedBigInteger('asset_coa_id')->nullable();
            }
            if (!Schema::hasColumn('asets', 'accum_depr_coa_id')) {
                $table->unsignedBigInteger('accum_depr_coa_id')->nullable();
            }
            if (!Schema::hasColumn('asets', 'expense_coa_id')) {
                $table->unsignedBigInteger('expense_coa_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->dropColumn(['asset_coa_id', 'accum_depr_coa_id', 'expense_coa_id']);
        });
    }
};
