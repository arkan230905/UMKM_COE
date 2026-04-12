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
        Schema::table('pembayaran_beban', function (Blueprint $table) {
            $table->foreignId('beban_operasional_id')->nullable()->constrained('beban_operasional')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_beban', function (Blueprint $table) {
            $table->dropForeign(['beban_operasional_id']);
            $table->dropColumn('beban_operasional_id');
        });
    }
};
