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
        // Tambah user_id ke paket_menus
        if (!Schema::hasColumn('paket_menus', 'user_id')) {
            Schema::table('paket_menus', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Tambah user_id ke ongkir_settings
        if (!Schema::hasColumn('ongkir_settings', 'user_id')) {
            Schema::table('ongkir_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key dan column dari paket_menus
        if (Schema::hasColumn('paket_menus', 'user_id')) {
            Schema::table('paket_menus', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        // Drop foreign key dan column dari ongkir_settings
        if (Schema::hasColumn('ongkir_settings', 'user_id')) {
            Schema::table('ongkir_settings', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
