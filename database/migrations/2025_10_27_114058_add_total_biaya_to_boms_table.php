<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            // Tambahkan kolom total_biaya
            if (!Schema::hasColumn('boms', 'total_biaya')) {
                $table->decimal('total_biaya', 15, 2)->after('jumlah')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            $table->dropColumn('total_biaya');
        });
    }
};
