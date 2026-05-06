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
        // Add user_id to proses_produksis table
        if (Schema::hasTable('proses_produksis') && !Schema::hasColumn('proses_produksis', 'user_id')) {
            Schema::table('proses_produksis', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                echo "Added user_id to proses_produksis table\n";
            });
        }

        // Add user_id to bop_proses table
        if (Schema::hasTable('bop_proses') && !Schema::hasColumn('bop_proses', 'user_id')) {
            Schema::table('bop_proses', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                echo "Added user_id to bop_proses table\n";
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove user_id from proses_produksis table
        if (Schema::hasTable('proses_produksis') && Schema::hasColumn('proses_produksis', 'user_id')) {
            Schema::table('proses_produksis', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        // Remove user_id from bop_proses table
        if (Schema::hasTable('bop_proses') && Schema::hasColumn('bop_proses', 'user_id')) {
            Schema::table('bop_proses', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
