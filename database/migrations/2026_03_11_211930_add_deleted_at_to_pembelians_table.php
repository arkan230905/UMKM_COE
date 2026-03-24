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
        if (!Schema::hasTable('pembelians')) {
            return;
        }

        if (!Schema::hasColumn('pembelians', 'deleted_at')) {
            Schema::table('pembelians', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('pembelians')) {
            return;
        }

        if (Schema::hasColumn('pembelians', 'deleted_at')) {
            Schema::table('pembelians', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
