<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First, check if the column doesn't exist
        if (!Schema::hasColumn('bahan_bakus', 'satuan_id')) {
            // Add the column as nullable first to avoid data loss
            Schema::table('bahan_bakus', function (Blueprint $table) {
                $table->unsignedBigInteger('satuan_id')->nullable()->after('id');
            });

            // If you want to set a default satuan_id for existing records, you can do it here
            // For example, setting it to 1 (assuming ID 1 exists in satuans table)
            // DB::table('bahan_bakus')->update(['satuan_id' => 1]);
            
            // After setting default values, you can make it not nullable
            Schema::table('bahan_bakus', function (Blueprint $table) {
                $table->unsignedBigInteger('satuan_id')->nullable(false)->change();
            });
        }
    }

    public function down()
    {
        // This is a one-way migration to prevent data loss
        // We're not dropping the column in the down method to prevent data loss
    }
};
