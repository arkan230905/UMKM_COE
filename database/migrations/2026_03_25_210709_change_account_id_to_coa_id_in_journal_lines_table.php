<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('journal_lines')) {

            Schema::table('journal_lines', function (Blueprint $table) {

                // kalau belum ada coa_id → buat
                if (!Schema::hasColumn('journal_lines', 'coa_id')) {
                    $table->unsignedBigInteger('coa_id')->nullable();
                }

            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('journal_lines')) {

            Schema::table('journal_lines', function (Blueprint $table) {

                if (Schema::hasColumn('journal_lines', 'coa_id')) {
                    $table->dropColumn('coa_id');
                }

            });
        }
    }
};