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
            // Add beban_operasional_id column if it doesn't exist
            if (!Schema::hasColumn('pembayaran_beban', 'beban_operasional_id')) {
                $table->unsignedBigInteger('beban_operasional_id')->nullable()->after('user_id');
                $table->foreign('beban_operasional_id')
                    ->references('id')
                    ->on('beban_operasional')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_beban', function (Blueprint $table) {
            // Drop foreign key and column
            if (Schema::hasColumn('pembayaran_beban', 'beban_operasional_id')) {
                $table->dropForeign(['beban_operasional_id']);
                $table->dropColumn('beban_operasional_id');
            }
        });
    }
};
