<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            if (!Schema::hasColumn('presensis', 'jumlah_jam')) {
                $table->decimal('jumlah_jam', 5, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            if (Schema::hasColumn('presensis', 'jumlah_jam')) {
                $table->dropColumn('jumlah_jam');
            }
        });
    }
};
