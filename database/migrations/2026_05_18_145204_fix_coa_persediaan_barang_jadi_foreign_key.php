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
        // Drop the foreign key constraint using raw SQL
        DB::statement('ALTER TABLE produksis DROP FOREIGN KEY IF EXISTS produksis_coa_persediaan_barang_jadi_id_foreign');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse - we're just removing a constraint
    }
};
