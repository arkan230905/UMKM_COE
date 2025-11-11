<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop foreign key constraint if exists
        Schema::table('bops', function (Blueprint $table) {
            if (Schema::hasColumn('bops', 'kode_akun')) {
                $table->dropForeign(['kode_akun']);
            }
        });

        // Add satuan_id to bahan_bakus
        if (!Schema::hasColumn('bahan_bakus', 'satuan_id')) {
            Schema::table('bahan_bakus', function (Blueprint $table) {
                $table->unsignedBigInteger('satuan_id')->nullable()->after('id');
                
                // Add foreign key constraint
                $table->foreign('satuan_id')
                      ->references('id')
                      ->on('satuans')
                      ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        // Drop foreign key constraint
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropForeign(['satuan_id']);
            $table->dropColumn('satuan_id');
        });
    }
};
