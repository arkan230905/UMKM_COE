<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if the columns exist before trying to modify them
        if (Schema::hasTable('pegawais')) {
            // Add the columns if they don't exist
            if (!Schema::hasColumn('pegawais', 'jenis_pegawai')) {
                Schema::table('pegawais', function (Blueprint $table) {
                    $table->string('jenis_pegawai')->nullable()->after('jabatan');
                });
            }
            
            if (!Schema::hasColumn('pegawais', 'gaji_pokok')) {
                Schema::table('pegawais', function (Blueprint $table) {
                    $table->decimal('gaji_pokok', 15, 2)->default(0)->after('jenis_pegawai');
                });
            }
            
            if (!Schema::hasColumn('pegawais', 'tunjangan')) {
                Schema::table('pegawais', function (Blueprint $table) {
                    $table->decimal('tunjangan', 15, 2)->default(0)->after('gaji_pokok');
                });
            }
        }
    }

    public function down()
    {
        // We won't remove the columns in the down method to prevent data loss
        // If you need to rollback, create a new migration to handle that
    }
};
