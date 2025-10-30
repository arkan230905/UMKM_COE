<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Buat tabel sementara dengan struktur baru
        Schema::create('pegawais_new', function (Blueprint $table) {
            $table->string('nomor_induk_pegawai')->primary();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('no_telp')->nullable();
            $table->text('alamat')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('jabatan')->nullable();
            $table->decimal('gaji', 15, 2)->nullable();
            $table->string('kategori_tenaga_kerja')->nullable();
            $table->decimal('gaji_pokok', 15, 2)->nullable();
            $table->decimal('tarif_per_jam', 15, 2)->nullable();
            $table->integer('jam_kerja_per_minggu')->nullable();
            $table->decimal('tunjangan', 15, 2)->default(0);
            $table->string('jenis_pegawai')->nullable();
            $table->timestamps();
        });

        // Salin data dari tabel lama ke tabel baru
        $pegawais = \DB::table('pegawais')->get();
        
        foreach ($pegawais as $pegawai) {
            $nomorInduk = 'EMP' . str_pad($pegawai->id, 3, '0', STR_PAD_LEFT);
            
            // Pastikan nomor induk unik
            $counter = 1;
            while (\DB::table('pegawais_new')->where('nomor_induk_pegawai', $nomorInduk)->exists()) {
                $nomorInduk = 'EMP' . str_pad($pegawai->id, 3, '0', STR_PAD_LEFT) . '_' . $counter;
                $counter++;
            }
            
            \DB::table('pegawais_new')->insert([
                'nomor_induk_pegawai' => $nomorInduk,
                'nama' => $pegawai->nama,
                'email' => $pegawai->email,
                'no_telp' => $pegawai->no_telp,
                'alamat' => $pegawai->alamat,
                'jenis_kelamin' => $pegawai->jenis_kelamin,
                'jabatan' => $pegawai->jabatan,
                'gaji' => $pegawai->gaji,
                'kategori_tenaga_kerja' => $pegawai->kategori_tenaga_kerja ?? null,
                'gaji_pokok' => $pegawai->gaji_pokok ?? null,
                'tarif_per_jam' => $pegawai->tarif_per_jam ?? null,
                'jam_kerja_per_minggu' => $pegawai->jam_kerja_per_minggu ?? null,
                'tunjangan' => $pegawai->tunjangan ?? 0,
                'jenis_pegawai' => $pegawai->jenis_pegawai ?? null,
                'created_at' => $pegawai->created_at,
                'updated_at' => $pegawai->updated_at,
            ]);
        }

        // Nonaktifkan foreign key checks sementara
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            // Dapatkan semua foreign key constraints yang merujuk ke tabel pegawais
            $allConstraints = \DB::select("
                SELECT 
                    TABLE_NAME,
                    CONSTRAINT_NAME
                FROM 
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE 
                    TABLE_SCHEMA = DATABASE() 
                    AND REFERENCED_TABLE_NAME = 'pegawais'
            ");
            
            // Kelompokkan constraint berdasarkan nama tabel
            $constraintsByTable = [];
            foreach ($allConstraints as $constraint) {
                if (!isset($constraintsByTable[$constraint->TABLE_NAME])) {
                    $constraintsByTable[$constraint->TABLE_NAME] = [];
                }
                $constraintsByTable[$constraint->TABLE_NAME][] = $constraint->CONSTRAINT_NAME;
            }
            
            // Hapus foreign key constraints
            foreach ($constraintsByTable as $table => $constraints) {
                if (!Schema::hasTable($table)) continue;
                
                foreach ($constraints as $constraint) {
                    try {
                        \DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `$constraint`");
                    } catch (\Exception $e) {
                        // Lewati error jika constraint tidak ada
                        if (strpos($e->getMessage(), 'check that it exists') === false) {
                            throw $e;
                        }
                    }
                }
            }
            
            // Hapus tabel lama dan ganti dengan yang baru
            Schema::dropIfExists('pegawais');
            Schema::rename('pegawais_new', 'pegawais');
            
            // Aktifkan kembali foreign key checks
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Exception $e) {
            // Jika terjadi error, pastikan untuk mengaktifkan kembali foreign key checks
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Kembalikan ke struktur lama jika diperlukan
        // Buat tabel sementara dengan struktur lama
        Schema::create('pegawais_old', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('no_telp');
            $table->text('alamat');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('jabatan');
            $table->decimal('gaji', 15, 2);
            $table->timestamps();
        });

        // Salin data kembali ke struktur lama
        $pegawais = \DB::table('pegawais')->get();
        
        foreach ($pegawais as $pegawai) {
            \DB::table('pegawais_old')->insert([
                'nama' => $pegawai->nama,
                'email' => $pegawai->email,
                'no_telp' => $pegawai->no_telp,
                'alamat' => $pegawai->alamat,
                'jenis_kelamin' => $pegawai->jenis_kelamin,
                'jabatan' => $pegawai->jabatan,
                'gaji' => $pegawai->gaji,
                'created_at' => $pegawai->created_at,
                'updated_at' => $pegawai->updated_at,
            ]);
        }

        // Nonaktifkan foreign key checks sementara
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            // Dapatkan semua foreign key constraints yang merujuk ke tabel pegawais_old
            $allConstraints = \DB::select("
                SELECT 
                    TABLE_NAME,
                    CONSTRAINT_NAME
                FROM 
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE 
                    TABLE_SCHEMA = DATABASE() 
                    AND REFERENCED_TABLE_NAME = 'pegawais_old'
            ");
            
            // Hapus foreign key constraints
            foreach ($allConstraints as $constraint) {
                try {
                    \DB::statement("ALTER TABLE `{$constraint->TABLE_NAME}` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
                } catch (\Exception $e) {
                    // Lewati error jika constraint tidak ada
                    if (strpos($e->getMessage(), 'check that it exists') === false) {
                        throw $e;
                    }
                }
            }
            
            // Hapus tabel baru dan ganti dengan yang lama
            Schema::dropIfExists('pegawais');
            Schema::rename('pegawais_old', 'pegawais');
            
            // Aktifkan kembali foreign key checks
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Exception $e) {
            // Jika terjadi error, pastikan untuk mengaktifkan kembali foreign key checks
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
    }
};
