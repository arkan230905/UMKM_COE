<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('asets', 'updated_by')) {
            Schema::table('asets', function (Blueprint $table) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('asets', 'updated_by')) {
            Schema::table('asets', function (Blueprint $table) {
                $table->dropColumn('updated_by');
            });
        }
    }
};
