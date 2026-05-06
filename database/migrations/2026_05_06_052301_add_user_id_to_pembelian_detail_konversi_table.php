<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pembelian_detail_konversi', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable()->constrained('users')->onDelete('cascade');
            $table->index(['user_id', 'pembelian_detail_id']);
        });

        // Update existing records with user_id from pembelian_details -> pembelians
        DB::statement("
            UPDATE pembelian_detail_konversi pdk
            INNER JOIN pembelian_details pd ON pdk.pembelian_detail_id = pd.id
            INNER JOIN pembelians p ON pd.pembelian_id = p.id
            SET pdk.user_id = p.user_id
            WHERE pdk.user_id IS NULL
        ");

        // Make user_id NOT NULL after updating existing records
        Schema::table('pembelian_detail_konversi', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian_detail_konversi', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'pembelian_detail_id']);
            $table->dropColumn('user_id');
        });
    }
};
