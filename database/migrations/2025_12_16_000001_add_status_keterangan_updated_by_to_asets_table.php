<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('asets', 'status')) {
            Schema::table('asets', function (Blueprint $table) {
                $table->string('status')->default('aktif')->after('tanggal_akuisisi');
            });
        }

        if (!Schema::hasColumn('asets', 'keterangan')) {
            Schema::table('asets', function (Blueprint $table) {
                $table->text('keterangan')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('asets', 'updated_by')) {
            Schema::table('asets', function (Blueprint $table) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

                if (Schema::hasTable('users')) {
                    $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('asets', 'updated_by')) {
            Schema::table('asets', function (Blueprint $table) {
                if (Schema::hasTable('users')) {
                    $table->dropForeign(['updated_by']);
                }
                $table->dropColumn('updated_by');
            });
        }

        if (Schema::hasColumn('asets', 'keterangan')) {
            Schema::table('asets', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }

        if (Schema::hasColumn('asets', 'status')) {
            Schema::table('asets', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
