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
        // Disable foreign key constraints
        Schema::disableForeignKeyConstraints();

        // Drop the unique constraint on kode_akun if it exists
        $indexes = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_akun_unique'");
        if (!empty($indexes)) {
            Schema::table('coas', function (Blueprint $table) {
                $table->dropUnique('coas_kode_akun_unique');
            });
        }

        // Add a composite unique constraint on kode_akun and company_id if it doesn't exist
        $compositeIndexes = DB::select("SHOW INDEX FROM coas WHERE Key_name = 'coas_kode_akun_company_unique'");
        if (empty($compositeIndexes)) {
            Schema::table('coas', function (Blueprint $table) {
                $table->unique(['kode_akun', 'company_id'], 'coas_kode_akun_company_unique');
            });
        }

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coas', function (Blueprint $table) {
            $table->dropUnique('coas_kode_akun_company_unique');
        });
        
        Schema::table('coas', function (Blueprint $table) {
            $table->unique('kode_akun', 'coas_kode_akun_unique');
        });
    }
};
