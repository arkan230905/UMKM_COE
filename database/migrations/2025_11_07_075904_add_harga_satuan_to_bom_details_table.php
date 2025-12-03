<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bom_details', function (Blueprint $table) {
            if (!Schema::hasColumn('bom_details', 'harga_satuan')) {
                $table->decimal('harga_satuan', 15, 2)->default(0)->after('satuan');
            }
            if (!Schema::hasColumn('bom_details', 'total')) {
                $table->decimal('total', 15, 2)->default(0)->after('harga_satuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bom_details', function (Blueprint $table) {
            $table->dropColumn(['harga_satuan', 'total']);
        });
    }
};
