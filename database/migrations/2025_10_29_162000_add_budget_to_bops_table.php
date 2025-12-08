<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skip - kolom sudah ada atau tabel berbeda struktur
        if (!Schema::hasTable('bops')) {
            return;
        }
        
        Schema::table('bops', function (Blueprint $table) {
            if (!Schema::hasColumn('bops', 'budget')) {
                $table->decimal('budget', 15, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('bops', 'periode')) {
                $table->string('periode', 7)->nullable();
            }
            if (!Schema::hasColumn('bops', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('bops')) {
            return;
        }
        
        Schema::table('bops', function (Blueprint $table) {
            $columns = ['budget', 'periode', 'is_active'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('bops', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
