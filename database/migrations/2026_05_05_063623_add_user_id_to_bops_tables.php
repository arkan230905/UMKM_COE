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
        // Add user_id to bops table
        Schema::table('bops', function (Blueprint $table) {
            if (!Schema::hasColumn('bops', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
                $table->index('user_id');
            }
        });

        // Add user_id to bop_proses table
        Schema::table('bop_proses', function (Blueprint $table) {
            if (!Schema::hasColumn('bop_proses', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
                $table->index('user_id');
            }
        });

        // Add user_id to beban_operasional table
        Schema::table('beban_operasional', function (Blueprint $table) {
            if (!Schema::hasColumn('beban_operasional', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
                $table->index('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove user_id from beban_operasional table
        Schema::table('beban_operasional', function (Blueprint $table) {
            if (Schema::hasColumn('beban_operasional', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        // Remove user_id from bop_proses table
        Schema::table('bop_proses', function (Blueprint $table) {
            if (Schema::hasColumn('bop_proses', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        // Remove user_id from bops table
        Schema::table('bops', function (Blueprint $table) {
            if (Schema::hasColumn('bops', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
