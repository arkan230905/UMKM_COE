<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            if (!Schema::hasColumn('jabatans', 'gaji')) {
                $table->decimal('gaji', 15, 2)->default(0)->after('tunjangan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            if (Schema::hasColumn('jabatans', 'gaji')) {
                $table->dropColumn('gaji');
            }
        });
    }
};
