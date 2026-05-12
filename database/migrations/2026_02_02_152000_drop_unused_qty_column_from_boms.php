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
        Schema::table('boms', function (Blueprint $table) {
            // Drop unused columns that were added but are not needed in current design
            $columnsToDrop = ['qty', 'satuan', 'harga_satuan', 'total_harga'];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('boms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            // Add back the columns if needed
            if (!Schema::hasColumn('boms', 'qty')) {
                $table->integer('qty')->nullable()->after('produk_id');
            }
            if (!Schema::hasColumn('boms', 'satuan')) {
                $table->string('satuan', 20)->nullable()->after('qty');
            }
            if (!Schema::hasColumn('boms', 'harga_satuan')) {
                $table->decimal('harga_satuan', 15, 2)->nullable()->after('satuan');
            }
            if (!Schema::hasColumn('boms', 'total_harga')) {
                $table->decimal('total_harga', 15, 2)->nullable()->after('harga_satuan');
            }
        });
    }
};
