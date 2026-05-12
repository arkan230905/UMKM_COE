<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            if (!Schema::hasColumn('jabatans', 'tunjangan_transport')) {
                $table->decimal('tunjangan_transport', 15, 2)->default(0)->after('tunjangan');
            }
            if (!Schema::hasColumn('jabatans', 'tunjangan_konsumsi')) {
                $table->decimal('tunjangan_konsumsi', 15, 2)->default(0)->after('tunjangan_transport');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            if (Schema::hasColumn('jabatans', 'tunjangan_transport')) {
                $table->dropColumn('tunjangan_transport');
            }
            if (Schema::hasColumn('jabatans', 'tunjangan_konsumsi')) {
                $table->dropColumn('tunjangan_konsumsi');
            }
        });
    }
};
