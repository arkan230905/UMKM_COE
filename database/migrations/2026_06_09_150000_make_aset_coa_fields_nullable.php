<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat field COA nullable karena akan di-assign otomatis saat posting
     */
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Ubah field COA menjadi nullable
            $table->unsignedBigInteger('asset_coa_id')->nullable()->change();
            $table->unsignedBigInteger('accum_depr_coa_id')->nullable()->change();
            $table->unsignedBigInteger('expense_coa_id')->nullable()->change();
            
            // Tambahkan field is_posted untuk tracking status posting
            if (!Schema::hasColumn('asets', 'is_posted')) {
                $table->boolean('is_posted')->default(false)->after('status');
            }
            
            // Tambahkan field posted_at untuk tracking kapan di-posting
            if (!Schema::hasColumn('asets', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('is_posted');
            }
        });
        
        echo "✓ Field COA aset sudah nullable\n";
        echo "✓ Field is_posted dan posted_at ditambahkan\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            // Kembalikan ke required (not nullable)
            $table->unsignedBigInteger('asset_coa_id')->nullable(false)->change();
            $table->unsignedBigInteger('accum_depr_coa_id')->nullable(false)->change();
            $table->unsignedBigInteger('expense_coa_id')->nullable(false)->change();
            
            // Hapus field is_posted dan posted_at
            if (Schema::hasColumn('asets', 'is_posted')) {
                $table->dropColumn('is_posted');
            }
            if (Schema::hasColumn('asets', 'posted_at')) {
                $table->dropColumn('posted_at');
            }
        });
    }
};
