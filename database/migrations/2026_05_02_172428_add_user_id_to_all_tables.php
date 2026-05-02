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
        // Tambah user_id ke kategori_produks
        if (!Schema::hasColumn('kategori_produks', 'user_id')) {
            Schema::table('kategori_produks', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke kategori_bahan_pendukung
        if (!Schema::hasColumn('kategori_bahan_pendukung', 'user_id')) {
            Schema::table('kategori_bahan_pendukung', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke journal_entries
        if (!Schema::hasColumn('journal_entries', 'user_id')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke ap_settlements
        if (!Schema::hasColumn('ap_settlements', 'user_id')) {
            Schema::table('ap_settlements', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke sales_returns
        if (!Schema::hasColumn('sales_returns', 'user_id')) {
            Schema::table('sales_returns', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke bom_job_costings
        if (!Schema::hasColumn('bom_job_costings', 'user_id')) {
            Schema::table('bom_job_costings', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke bom_job_bahan_pendukung
        if (!Schema::hasColumn('bom_job_bahan_pendukung', 'user_id')) {
            Schema::table('bom_job_bahan_pendukung', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke bom_job_bop
        if (!Schema::hasColumn('bom_job_bop', 'user_id')) {
            Schema::table('bom_job_bop', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke bom_job_btkl
        if (!Schema::hasColumn('bom_job_btkl', 'user_id')) {
            Schema::table('bom_job_btkl', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke bom_proses
        if (!Schema::hasColumn('bom_proses', 'user_id')) {
            Schema::table('bom_proses', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke bops
        if (!Schema::hasColumn('bops', 'user_id')) {
            Schema::table('bops', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke bop_lainnya
        if (!Schema::hasColumn('bop_lainnya', 'user_id')) {
            Schema::table('bop_lainnya', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        // Tambah user_id ke komponen_bops
        if (!Schema::hasColumn('komponen_bops', 'user_id')) {
            Schema::table('komponen_bops', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('kategori_produks', 'user_id')) {
            Schema::table('kategori_produks', function (Blueprint $table) {
                $table->dropForeign(['kategori_produks_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('kategori_bahan_pendukung', 'user_id')) {
            Schema::table('kategori_bahan_pendukung', function (Blueprint $table) {
                $table->dropForeign(['kategori_bahan_pendukung_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('journal_entries', 'user_id')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->dropForeign(['journal_entries_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('ap_settlements', 'user_id')) {
            Schema::table('ap_settlements', function (Blueprint $table) {
                $table->dropForeign(['ap_settlements_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('sales_returns', 'user_id')) {
            Schema::table('sales_returns', function (Blueprint $table) {
                $table->dropForeign(['sales_returns_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('bom_job_costings', 'user_id')) {
            Schema::table('bom_job_costings', function (Blueprint $table) {
                $table->dropForeign(['bom_job_costings_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('bom_job_bahan_pendukung', 'user_id')) {
            Schema::table('bom_job_bahan_pendukung', function (Blueprint $table) {
                $table->dropForeign(['bom_job_bahan_pendukung_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('bom_job_bop', 'user_id')) {
            Schema::table('bom_job_bop', function (Blueprint $table) {
                $table->dropForeign(['bom_job_bop_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('bom_job_btkl', 'user_id')) {
            Schema::table('bom_job_btkl', function (Blueprint $table) {
                $table->dropForeign(['bom_job_btkl_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('bom_proses', 'user_id')) {
            Schema::table('bom_proses', function (Blueprint $table) {
                $table->dropForeign(['bom_proses_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('bops', 'user_id')) {
            Schema::table('bops', function (Blueprint $table) {
                $table->dropForeign(['bops_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('bop_lainnya', 'user_id')) {
            Schema::table('bop_lainnya', function (Blueprint $table) {
                $table->dropForeign(['bop_lainnya_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('komponen_bops', 'user_id')) {
            Schema::table('komponen_bops', function (Blueprint $table) {
                $table->dropForeign(['komponen_bops_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }

    }
};
