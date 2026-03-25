<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing hpp column to produks table
     */
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Add hpp column (Harga Pokok Produksi)
            $table->decimal('hpp', 15, 2)->default(0)->after('harga_pokok')
                ->comment('Harga Pokok Produksi per unit');
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('hpp');
        });
    }
};
