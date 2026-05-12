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
        if (Schema::hasTable('journal_lines')) {
            Schema::table('journal_lines', function (Blueprint $table) {
                // Hanya buat coa_id kalau belum ada sama sekali
                if (!Schema::hasColumn('journal_lines', 'coa_id')) {
                    $table->unsignedBigInteger('coa_id')->nullable()->after('journal_entry_id');
                }
                
                // Hapus account_id kalau masih ada
                if (Schema::hasColumn('journal_lines', 'account_id')) {
                    $table->dropColumn('account_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('journal_lines')) {
            Schema::table('journal_lines', function (Blueprint $table) {
                // Kembalikan ke account_id kalau coa_id ada
                if (Schema::hasColumn('journal_lines', 'coa_id')) {
                    $table->unsignedBigInteger('account_id')->nullable()->after('journal_entry_id');
                    $table->dropColumn('coa_id');
                }
            });
        }
    }
};
