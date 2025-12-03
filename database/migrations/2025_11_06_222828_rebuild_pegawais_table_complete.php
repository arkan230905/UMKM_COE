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
        // Drop the temporary table if it exists
        Schema::dropIfExists('pegawais_temp');
        
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

        // Copy data to temporary table if pegawais table exists
        if (Schema::hasTable('pegawais')) {
            $columns = ['email', 'nama', 'no_telepon', 'alamat', 'jabatan', 'gaji', 'jenis_kelamin', 'created_at', 'updated_at'];
            $existingColumns = [];
            
            // Only include columns that exist in the original table
            foreach ($columns as $column) {
                if (Schema::hasColumn('pegawais', $column)) {
                    $existingColumns[] = $column;
                }
            }
            
            if (!empty($existingColumns)) {
                $selectColumns = implode(', ', $existingColumns);
                $insertColumns = implode(', ', array_map(function($col) { 
                    return '`' . $col . '`'; 
                }, $existingColumns));
                
                DB::statement("INSERT INTO pegawais_temp ($insertColumns) SELECT $selectColumns FROM pegawais");
            }
        }

        // Drop the original table if it exists
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

        // Copy data back from temporary table if it has data
        if (Schema::hasTable('pegawais_temp') && DB::table('pegawais_temp')->count() > 0) {
            $pegawais = DB::table('pegawais_temp')->get();
            
            foreach ($pegawais as $index => $pegawai) {
                $kode = 'PGW' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                
                $data = [
                    'kode_pegawai' => $kode,
                    'nama' => $pegawai->nama ?? null,
                    'email' => $pegawai->email ?? null,
                    'no_telepon' => $pegawai->no_telepon ?? null,
                    'alamat' => $pegawai->alamat ?? null,
                    'jabatan' => $pegawai->jabatan ?? null,
                    'gaji' => $pegawai->gaji ?? 0,
                    'jenis_kelamin' => $pegawai->jenis_kelamin ?? null,
                    'created_at' => $pegawai->created_at ?? now(),
                    'updated_at' => $pegawai->updated_at ?? now(),
                ];
                
                // Only include fields that exist in the source data
                $data = array_filter($data, function($value) {
                    return $value !== null;
                });
                
                DB::table('pegawais')->insert($data);
            }
        }

        // Drop the temporary table
        Schema::dropIfExists('pegawais_temp');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a potentially destructive operation, so we'll leave the table as is
        // If you need to rollback, you should create a new migration to handle it
    }
};
