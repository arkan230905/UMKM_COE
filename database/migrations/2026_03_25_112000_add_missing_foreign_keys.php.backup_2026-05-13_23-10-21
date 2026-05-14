<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing foreign key constraints to old tables
     * Improve database integrity and referential integrity
     */
    public function up(): void
    {
        // Add foreign keys to asets table if they don't exist
        if (Schema::hasTable('asets')) {
            Schema::table('asets', function (Blueprint $table) {
                // Check if foreign key doesn't exist before adding
                if (!Schema::hasColumn('asets', 'kategori_aset_id')) {
                    $table->foreignId('kategori_aset_id')->nullable()->after('kategori')->constrained('kategori_asets');
                }
                
                // Add COA relationships for depreciation
                if (!Schema::hasColumn('asets', 'coa_id')) {
                    $table->foreignId('coa_id')->nullable()->after('updated_by')->constrained('coas');
                }
                if (!Schema::hasColumn('asets', 'depr_expense_coa_id')) {
                    $table->foreignId('depr_expense_coa_id')->nullable()->after('coa_id')->constrained('coas');
                }
                if (!Schema::hasColumn('asets', 'depr_accum_coa_id')) {
                    $table->foreignId('depr_accum_coa_id')->nullable()->after('depr_expense_coa_id')->constrained('coas');
                }
            });
        }

        // Add foreign keys to pembelians table
        if (Schema::hasTable('pembelians')) {
            Schema::table('pembelians', function (Blueprint $table) {
                if (!Schema::hasColumn('pembelians', 'vendor_id')) {
                    $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors');
                }
                if (!Schema::hasColumn('pembelians', 'coa_id')) {
                    $table->foreignId('coa_id')->nullable()->after('vendor_id')->constrained('coas');
                }
            });
        }

        // Add foreign keys to penjualans table  
        if (Schema::hasTable('penjualans')) {
            Schema::table('penjualans', function (Blueprint $table) {
                if (!Schema::hasColumn('penjualans', 'pelanggan_id')) {
                    $table->foreignId('pelanggan_id')->nullable()->after('id')->constrained('pelanggans');
                }
                if (!Schema::hasColumn('penjualans', 'coa_id')) {
                    $table->foreignId('coa_id')->nullable()->after('pelanggan_id')->constrained('coas');
                }
            });
        }

        // Add foreign keys to boms table
        if (Schema::hasTable('boms')) {
            Schema::table('boms', function (Blueprint $table) {
                if (!Schema::hasColumn('boms', 'produk_id')) {
                    $table->foreignId('produk_id')->nullable()->after('id')->constrained('produks');
                }
                if (!Schema::hasColumn('boms', 'coa_id')) {
                    $table->foreignId('coa_id')->nullable()->after('produk_id')->constrained('coas');
                }
            });
        }

        // Add foreign keys to produks table
        if (Schema::hasTable('produks')) {
            Schema::table('produks', function (Blueprint $table) {
                if (!Schema::hasColumn('produks', 'kategori_produk_id')) {
                    $table->foreignId('kategori_produk_id')->nullable()->after('nama_produk')->constrained('kategori_produks');
                }
                if (!Schema::hasColumn('produks', 'coa_persediaan_id')) {
                    $table->foreignId('coa_persediaan_id')->nullable()->after('kategori_produk_id')->constrained('coas');
                }
                if (!Schema::hasColumn('produks', 'coa_hpp_id')) {
                    $table->foreignId('coa_hpp_id')->nullable()->after('coa_persediaan_id')->constrained('coas');
                }
            });
        }
    }

    public function down(): void
    {
        // Remove added foreign keys and columns
        Schema::table('asets', function (Blueprint $table) {
            $table->dropForeign(['kategori_aset_id']);
            $table->dropForeign(['coa_id']);
            $table->dropForeign(['depr_expense_coa_id']);
            $table->dropForeign(['depr_accum_coa_id']);
            $table->dropColumn(['kategori_aset_id', 'coa_id', 'depr_expense_coa_id', 'depr_accum_coa_id']);
        });

        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['coa_id']);
            $table->dropColumn(['vendor_id', 'coa_id']);
        });

        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropForeign(['pelanggan_id']);
            $table->dropForeign(['coa_id']);
            $table->dropColumn(['pelanggan_id', 'coa_id']);
        });

        Schema::table('boms', function (Blueprint $table) {
            $table->dropForeign(['produk_id']);
            $table->dropForeign(['coa_id']);
            $table->dropColumn(['produk_id', 'coa_id']);
        });

        Schema::table('produks', function (Blueprint $table) {
            $table->dropForeign(['kategori_produk_id']);
            $table->dropForeign(['coa_persediaan_id']);
            $table->dropForeign(['coa_hpp_id']);
            $table->dropColumn(['kategori_produk_id', 'coa_persediaan_id', 'coa_hpp_id']);
        });
    }
};
