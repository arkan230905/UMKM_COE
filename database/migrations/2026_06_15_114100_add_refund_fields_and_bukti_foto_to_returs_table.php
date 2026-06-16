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
        Schema::table('returs', function (Blueprint $table) {
            if (!Schema::hasColumn('returs', 'metode_refund')) {
                $table->string('metode_refund')->nullable()->after('kompensasi');
            }
            if (!Schema::hasColumn('returs', 'bukti_foto')) {
                $table->string('bukti_foto')->nullable()->after('metode_refund');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            if (Schema::hasColumn('returs', 'metode_refund')) {
                $table->dropColumn('metode_refund');
            }
            if (Schema::hasColumn('returs', 'bukti_foto')) {
                $table->dropColumn('bukti_foto');
            }
        });
    }
};
