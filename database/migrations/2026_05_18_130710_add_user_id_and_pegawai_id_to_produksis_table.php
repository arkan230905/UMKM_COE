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
        Schema::table('produksis', function (Blueprint $table) {
            // Add user_id for multi-tenant isolation
            if (!Schema::hasColumn('produksis', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
            }
            
            // Add pegawai_id to track which employee produced this
            if (!Schema::hasColumn('produksis', 'pegawai_id')) {
                $table->unsignedBigInteger('pegawai_id')->nullable()->after('user_id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksis', function (Blueprint $table) {
            if (Schema::hasColumn('produksis', 'user_id')) {
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }
            
            if (Schema::hasColumn('produksis', 'pegawai_id')) {
                $table->dropIndex(['pegawai_id']);
                $table->dropColumn('pegawai_id');
            }
        });
    }
};
