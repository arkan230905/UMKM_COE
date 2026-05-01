<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_return_items') && Schema::hasColumn('purchase_return_items', 'bahan_baku_id')) {
            Schema::table('purchase_return_items', function (Blueprint $table) {
                $table->unsignedBigInteger('bahan_baku_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // No-op: reverting nullable is risky
    }
};
