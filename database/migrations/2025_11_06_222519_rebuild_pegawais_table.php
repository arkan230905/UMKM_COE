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
        // Create a temporary table to store existing data
        Schema::create('pegawais_temp', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('nama');
            $table->string('no_telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->string('jabatan')->nullable();
            $table->decimal('gaji', 15, 2)->default(0);
            $table->string('jenis_kelamin', 1)->nullable();
            $table->timestamps();
        });

        // Copy data to temporary table
        DB::statement('INSERT INTO pegawais_temp (email, nama, no_telepon, alamat, jabatan, gaji, jenis_kelamin, created_at, updated_at) 
                      SELECT email, nama, no_telepon, alamat, jabatan, gaji, jenis_kelamin, created_at, updated_at FROM pegawais');

        // Drop the original table
        Schema::dropIfExists('pegawais');

        // Create the new table with all required columns
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pegawai', 20)->unique()->nullable();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('no_telepon', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('jenis_kelamin', 1)->nullable();
            $table->string('jabatan')->nullable();
            $table->decimal('gaji', 15, 2)->default(0);
            $table->string('nama_bank', 100)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->enum('kategori', ['BTKL', 'BTKTL'])->default('BTKL');
            $table->decimal('asuransi', 15, 2)->default(0);
            $table->decimal('tarif', 15, 2)->default(0);
            $table->decimal('tunjangan', 15, 2)->default(0);
            $table->timestamps();
        });

        // Copy data back from temporary table
        $pegawais = DB::table('pegawais_temp')->get();
        foreach ($pegawais as $index => $pegawai) {
            $kode = 'PGW' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            DB::table('pegawais')->insert([
                'kode_pegawai' => $kode,
                'nama' => $pegawai->nama,
                'email' => $pegawai->email,
                'no_telepon' => $pegawai->no_telepon,
                'alamat' => $pegawai->alamat,
                'jabatan' => $pegawai->jabatan,
                'gaji' => $pegawai->gaji,
                'jenis_kelamin' => $pegawai->jenis_kelamin,
                'created_at' => $pegawai->created_at,
                'updated_at' => $pegawai->updated_at,
            ]);
        }

        // Drop the temporary table
        Schema::dropIfExists('pegawais_temp');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create a temporary table to store existing data
        Schema::create('pegawais_temp', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('nama');
            $table->string('no_telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->string('jabatan')->nullable();
            $table->decimal('gaji', 15, 2)->default(0);
            $table->string('jenis_kelamin', 1)->nullable();
            $table->timestamps();
        });

        // Copy data to temporary table
        DB::statement('INSERT INTO pegawais_temp (email, nama, no_telepon, alamat, jabatan, gaji, jenis_kelamin, created_at, updated_at) 
                      SELECT email, nama, no_telepon, alamat, jabatan, gaji, jenis_kelamin, created_at, updated_at FROM pegawais');

        // Drop the new table
        Schema::dropIfExists('pegawais');

        // Recreate the original table
        Schema::create('pegawais', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('nama');
            $table->string('no_telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->string('jabatan')->nullable();
            $table->decimal('gaji', 15, 2)->default(0);
            $table->string('jenis_kelamin', 1)->nullable();
            $table->timestamps();
        });

        // Copy data back from temporary table
        DB::statement('INSERT INTO pegawais (email, nama, no_telepon, alamat, jabatan, gaji, jenis_kelamin, created_at, updated_at) 
                      SELECT email, nama, no_telepon, alamat, jabatan, gaji, jenis_kelamin, created_at, updated_at FROM pegawais_temp');

        // Drop the temporary table
        Schema::dropIfExists('pegawais_temp');
    }
};
