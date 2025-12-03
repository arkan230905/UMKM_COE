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
        // Tambah kolom yang hilang di tabel boms
        Schema::table('boms', function (Blueprint $table) {
            if (!Schema::hasColumn('boms', 'satuan_resep')) {
                $table->string('satuan_resep')->nullable()->after('jumlah');
            }
            if (!Schema::hasColumn('boms', 'total_biaya')) {
                $table->decimal('total_biaya', 15, 2)->default(0)->after('satuan_resep');
            }
            if (!Schema::hasColumn('boms', 'btkl_per_unit')) {
                $table->decimal('btkl_per_unit', 15, 2)->default(0)->after('total_biaya');
            }
            if (!Schema::hasColumn('boms', 'bop_rate')) {
                $table->decimal('bop_rate', 15, 2)->default(0)->after('btkl_per_unit');
            }
            if (!Schema::hasColumn('boms', 'bop_per_unit')) {
                $table->decimal('bop_per_unit', 15, 2)->default(0)->after('bop_rate');
            }
            if (!Schema::hasColumn('boms', 'total_btkl')) {
                $table->decimal('total_btkl', 15, 2)->default(0)->after('bop_per_unit');
            }
            if (!Schema::hasColumn('boms', 'total_bop')) {
                $table->decimal('total_bop', 15, 2)->default(0)->after('total_btkl');
            }
            if (!Schema::hasColumn('boms', 'periode')) {
                $table->string('periode')->nullable()->after('total_bop');
            }
        });

        // Tambah kolom kategori di tabel bom_details
        Schema::table('bom_details', function (Blueprint $table) {
            if (!Schema::hasColumn('bom_details', 'kategori')) {
                $table->string('kategori')->default('BOP')->after('total_harga');
            }
            if (!Schema::hasColumn('bom_details', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('kategori');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            $table->dropColumn([
                'satuan_resep',
                'total_biaya',
                'btkl_per_unit',
                'bop_rate',
                'bop_per_unit',
                'total_btkl',
                'total_bop',
                'periode'
            ]);
        });

        Schema::table('bom_details', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'keterangan']);
        });
    }
};
