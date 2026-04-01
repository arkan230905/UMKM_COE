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
        // Fix foreign key constraints for coa_period_balances table
        if (Schema::hasTable('coa_period_balances')) {
            Schema::table('coa_period_balances', function (Blueprint $table) {
                try {
                    $table->dropForeign(['kode_akun']);
                    $table->foreign('kode_akun')->references('kode_akun')->on('coas')->onDelete('cascade')->onUpdate('cascade');
                } catch (\Exception $e) {
                    // Ignore if constraint doesn't exist
                }
            });
        }
        
        // Fix foreign key constraints for bops table if it exists
        if (Schema::hasTable('bops')) {
            Schema::table('bops', function (Blueprint $table) {
                try {
                    $table->dropForeign(['kode_akun']);
                    $table->foreign('kode_akun')->references('kode_akun')->on('coas')->onDelete('cascade')->onUpdate('cascade');
                } catch (\Exception $e) {
                    // Ignore if constraint doesn't exist
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert coa_period_balances foreign key constraints
        if (Schema::hasTable('coa_period_balances')) {
            Schema::table('coa_period_balances', function (Blueprint $table) {
                try {
                    $table->dropForeign(['kode_akun']);
                    $table->foreign('kode_akun')->references('kode_akun')->on('coas');
                } catch (\Exception $e) {
                    // Ignore if constraint doesn't exist
                }
            });
        }
        
        // Revert bops foreign key constraints
        if (Schema::hasTable('bops')) {
            Schema::table('bops', function (Blueprint $table) {
                try {
                    $table->dropForeign(['kode_akun']);
                    $table->foreign('kode_akun')->references('kode_akun')->on('coas');
                } catch (\Exception $e) {
                    // Ignore if constraint doesn't exist
                }
            });
        }
    }
};
