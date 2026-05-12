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
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->decimal('budget', 15, 2)->default(0)->comment('Budget BOP per shift (8 jam)');
            $table->decimal('aktual', 15, 2)->default(0)->comment('Aktual BOP yang sudah terpakai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bop_proses', function (Blueprint $table) {
            $table->dropColumn(['budget', 'aktual']);
        });
    }
};
