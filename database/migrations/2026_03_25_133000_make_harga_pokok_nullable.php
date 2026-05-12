<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make harga_pokok column nullable to allow empty values
     */
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            // Make harga_pokok nullable so it can be NULL when no production data exists
            $table->decimal('harga_pokok', 15, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->decimal('harga_pokok', 15, 2)->default(0)->change();
        });
    }
};
