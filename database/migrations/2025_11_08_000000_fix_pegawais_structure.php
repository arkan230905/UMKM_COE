<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop foreign keys first
        Schema::table('presensis', function (Blueprint $table) {
            if (Schema::hasColumn('presensis', 'pegawai_id')) {
                $table->dropForeign(['pegawai_id']);
            }
        });

        // Create a temporary table to store existing data
        if (!Schema::hasTable('temp_pegawais')) {
            Schema::create('temp_pegawais', function (Blueprint $table) {
                $table->id();
                $table->string('kode_pegawai')->unique();
                $table->string('nama');
                $table->string('email')->unique();
                $table->string('no_telp');
                $table->text('alamat');
                $table->enum('jenis_kelamin', ['L', 'P']);
                $table->string('jabatan');
                $table->string('jenis_pegawai')->default('btkl');
                $table->decimal('gaji_pokok', 15, 2)->default(0);
                $table->decimal('tunjangan', 15, 2)->default(0);
                $table->timestamps();
            });

            // Copy existing data to temp table
            \DB::statement("INSERT INTO temp_pegawais (id, kode_pegawai, nama, email, no_telp, alamat, jenis_kelamin, jabatan, jenis_pegawai, gaji_pokok, tunjangan, created_at, updated_at)
                SELECT id, kode_pegawai, nama, email, IFNULL(no_telepon, '') as no_telp, alamat, IFNULL(jenis_kelamin, 'L') as jenis_kelamin, 
                       jabatan, IFNULL(LOWER(jenis_pegawai), 'btkl') as jenis_pegawai, 
                       IFNULL(gaji_pokok, IFNULL(gaji, 0)) as gaji_pokok, 
                       IFNULL(tunjangan, 0) as tunjangan, 
                       created_at, updated_at 
                FROM pegawais");
        }

        // Drop existing table
        Schema::dropIfExists('pegawais');

        // Create new table with correct structure
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pegawai')->unique();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('no_telp');
            $table->text('alamat');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('jabatan');
            $table->string('jenis_pegawai')->default('btkl');
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tunjangan', 15, 2)->default(0);
            $table->timestamps();
        });

        // Copy data back from temp table
        if (Schema::hasTable('temp_pegawais')) {
            \DB::statement("INSERT INTO pegawais (id, kode_pegawai, nama, email, no_telp, alamat, jenis_kelamin, jabatan, jenis_pegawai, gaji_pokok, tunjangan, created_at, updated_at)
                SELECT id, kode_pegawai, nama, email, no_telp, alamat, jenis_kelamin, jabatan, jenis_pegawai, gaji_pokok, tunjangan, created_at, updated_at 
                FROM temp_pegawais");
            
            // Drop temp table
            Schema::dropIfExists('temp_pegawais');
        }

        // Recreate foreign key
        Schema::table('presensis', function (Blueprint $table) {
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');
        });
    }

    public function down()
    {
        // No need to implement down as this is a one-time fix
    }
};
