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
        Schema::table('produks', function (Blueprint $table) {
            // Add show_in_catalog column - default 1 (show in catalog)
            // Place after 'foto' column if exists, otherwise just add it
            if (Schema::hasColumn('produks', 'foto')) {
                $table->boolean('show_in_catalog')->default(1)->after('foto');
            } else {
                $table->boolean('show_in_catalog')->default(1);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('show_in_catalog');
        });
    }
};
