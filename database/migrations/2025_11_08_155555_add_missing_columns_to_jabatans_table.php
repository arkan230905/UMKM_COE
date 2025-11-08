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
        Schema::table('jabatans', function (Blueprint $table) {
            if (!Schema::hasColumn('jabatans', 'asuransi')) {
                $table->decimal('asuransi', 15, 2)->default(0)->after('tunjangan');
            }
            if (!Schema::hasColumn('jabatans', 'gaji')) {
                $table->decimal('gaji', 15, 2)->default(0)->after('asuransi');
            }
            if (!Schema::hasColumn('jabatans', 'tarif')) {
                $table->decimal('tarif', 15, 2)->default(0)->after('gaji');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $columns = ['asuransi', 'gaji', 'tarif'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('jabatans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
