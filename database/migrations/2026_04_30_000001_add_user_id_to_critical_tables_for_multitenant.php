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
        // Add user_id to critical tables for multi-tenant isolation
        $tables = [
            'produks' => 'Products',
            'penjualans' => 'Sales',
            'pembelians' => 'Purchases',
            'produksis' => 'Production',
            'presensis' => 'Attendance',
            'penggajians' => 'Payroll',
            'asets' => 'Assets',
            'jurnal_umum' => 'General Ledger',
            'penjualan_details' => 'Sales Details',
            'pembelian_details' => 'Purchase Details',
            'produksi_details' => 'Production Details',
            'boms' => 'Bill of Materials',
            'bom_details' => 'BOM Details',
            'stock_movements' => 'Stock Movements',
            'stock_layers' => 'Stock Layers',
            'returs' => 'Returns',
            'retur_penjualans' => 'Sales Returns',
            'detail_retur_penjualans' => 'Sales Return Details',
            'purchase_returns' => 'Purchase Returns',
            'purchase_return_items' => 'Purchase Return Items',
            'pelunasan_utangs' => 'Debt Payments',
            'pembayaran_bebans' => 'Expense Payments',
            'beban_operasional' => 'Operational Expenses',
            'catalog_photos' => 'Catalog Photos',
            'catalog_sections' => 'Catalog Sections',
            'favorites' => 'Favorites',
            'paket_menus' => 'Package Menus',
            'ongkir_settings' => 'Shipping Settings',
            'bukti_pembayaran' => 'Payment Proofs',
            'pelanggans' => 'Customers',
            'kartu_stok' => 'Stock Cards'
        ];

        foreach ($tables as $table => $description) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $table) use ($description) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
                echo "Added user_id to {$table} ({$description})\n";
            }
        }

        // Add company_id to users table if not exists
        if (!Schema::hasColumn('users', 'company_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('perusahaan_id')->index();
                $table->foreign('company_id')->references('id')->on('perusahaan')->onDelete('cascade');
            });
            echo "Added company_id to users table\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'produks', 'penjualans', 'pembelians', 'produksis', 'presensis', 'penggajians',
            'asets', 'jurnal_umum', 'penjualan_details', 'pembelian_details', 'produksi_details',
            'boms', 'bom_details', 'stock_movements', 'stock_layers', 'returs', 'retur_penjualans',
            'detail_retur_penjualans', 'purchase_returns', 'purchase_return_items', 'pelunasan_utangs',
            'pembayaran_bebans', 'beban_operasional', 'catalog_photos', 'catalog_sections',
            'favorites', 'paket_menus', 'ongkir_settings', 'bukti_pembayaran', 'pelanggans', 'kartu_stok'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropIndex(['user_id']);
                    $table->dropColumn('user_id');
                });
            }
        }

        if (Schema::hasColumn('users', 'company_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropIndex(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
