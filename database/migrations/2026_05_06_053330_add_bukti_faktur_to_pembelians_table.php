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
        Schema::table('pembelians', function (Blueprint $table) {
            if (!Schema::hasColumn('pembelians', 'bukti_faktur')) {
                $table->string('bukti_faktur', 255)->nullable()->after('nomor_faktur');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            if (Schema::hasColumn('pembelians', 'bukti_faktur')) {
                $table->dropColumn('bukti_faktur');
            }
        });
    }
};
