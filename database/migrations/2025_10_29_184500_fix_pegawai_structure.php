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
        // Nonaktifkan foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // 1. Buat tabel sementara untuk menyimpan data
            Schema::create('temp_pegawais', function (Blueprint $table) {
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

            // 2. Salin data dari tabel lama ke tabel sementara
            $pegawais = \DB::table('pegawais')->get();
            
            foreach ($pegawais as $pegawai) {
                $nomorInduk = 'EMP' . str_pad($pegawai->id, 3, '0', STR_PAD_LEFT);
                
                // Pastikan nomor induk unik
                $counter = 1;
                while (\DB::table('temp_pegawais')->where('nomor_induk_pegawai', $nomorInduk)->exists()) {
                    $nomorInduk = 'EMP' . str_pad($pegawai->id, 3, '0', STR_PAD_LEFT) . '_' . $counter;
                    $counter++;
                }
                
                \DB::table('temp_pegawais')->insert([
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

            // 3. Hapus tabel lama dan ganti dengan yang baru
            Schema::dropIfExists('pegawais');
            Schema::rename('temp_pegawais', 'pegawais');

            // 4. Update foreign key di tabel presensis
            if (Schema::hasTable('presensis')) {
                // Hapus foreign key constraint jika ada
                $constraints = \DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'presensis'
                    AND COLUMN_NAME = 'pegawai_id'
                    AND REFERENCED_TABLE_NAME = 'pegawais'
                ");

                foreach ($constraints as $constraint) {
                    try {
                        \DB::statement("ALTER TABLE `presensis` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
                    } catch (\Exception $e) {
                        // Lewati error jika constraint tidak ada
                        if (strpos($e->getMessage(), 'check that it exists') === false) {
                            throw $e;
                        }
                    }
                }

                // Ubah tipe kolom pegawai_id
                \DB::statement('ALTER TABLE `presensis` MODIFY `pegawai_id` VARCHAR(20) NOT NULL');

                // Tambahkan foreign key constraint baru
                \DB::statement('ALTER TABLE `presensis` ADD CONSTRAINT `presensis_pegawai_id_foreign` 
                    FOREIGN KEY (`pegawai_id`) REFERENCES `pegawais` (`nomor_induk_pegawai`) ON DELETE CASCADE');
            }

            // Aktifkan kembali foreign key checks
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        } catch (\Exception $e) {
            // Pastikan untuk mengaktifkan kembali foreign key checks jika terjadi error
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Nonaktifkan foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // Buat tabel sementara dengan struktur lama
            Schema::create('temp_pegawais', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('email')->unique();
                $table->string('no_telp');
                $table->text('alamat');
                $table->enum('jenis_kelamin', ['L', 'P']);
                $table->string('jabatan');
                $table->decimal('gaji', 15, 2);
                $table->string('kategori_tenaga_kerja')->nullable();
                $table->decimal('gaji_pokok', 15, 2)->nullable();
                $table->decimal('tarif_per_jam', 15, 2)->nullable();
                $table->integer('jam_kerja_per_minggu')->nullable();
                $table->decimal('tunjangan', 15, 2)->default(0);
                $table->string('jenis_pegawai')->nullable();
                $table->timestamps();
            });

            // Salin data kembali ke struktur lama
            $pegawais = \DB::table('pegawais')->get();
            
            foreach ($pegawais as $pegawai) {
                \DB::table('temp_pegawais')->insert([
                    'nama' => $pegawai->nama,
                    'email' => $pegawai->email,
                    'no_telp' => $pegawai->no_telp,
                    'alamat' => $pegawai->alamat,
                    'jenis_kelamin' => $pegawai->jenis_kelamin,
                    'jabatan' => $pegawai->jabatan,
                    'gaji' => $pegawai->gaji,
                    'kategori_tenaga_kerja' => $pegawai->kategori_tenaga_kerja,
                    'gaji_pokok' => $pegawai->gaji_pokok,
                    'tarif_per_jam' => $pegawai->tarif_per_jam,
                    'jam_kerja_per_minggu' => $pegawai->jam_kerja_per_minggu,
                    'tunjangan' => $pegawai->tunjangan,
                    'jenis_pegawai' => $pegawai->jenis_pegawai,
                    'created_at' => $pegawai->created_at,
                    'updated_at' => $pegawai->updated_at,
                ]);
            }

            // Hapus tabel lama dan ganti dengan yang baru
            Schema::dropIfExists('pegawais');
            Schema::rename('temp_pegawais', 'pegawais');

            // Update foreign key di tabel presensis
            if (Schema::hasTable('presensis')) {
                // Hapus foreign key constraint jika ada
                $constraints = \DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'presensis'
                    AND COLUMN_NAME = 'pegawai_id'
                    AND REFERENCED_TABLE_NAME = 'pegawais'
                ");
                
                foreach ($constraints as $constraint) {
                    try {
                        $constraintName = $constraint->CONSTRAINT_NAME;
                        \DB::statement("ALTER TABLE `presensis` DROP FOREIGN KEY `$constraintName`");
                    } catch (\Exception $e) {
                        // Lewati error jika constraint tidak ada
                        if (strpos($e->getMessage(), 'check that it exists') === false) {
                            throw $e;
                        }
                    }
                }

                // Ubah tipe kolom pegawai_id kembali ke integer
                \DB::statement('ALTER TABLE `presensis` MODIFY `pegawai_id` BIGINT UNSIGNED NOT NULL');

                // Tambahkan foreign key constraint baru
                if (Schema::hasColumn('pegawais', 'id')) {
                    \DB::statement('ALTER TABLE `presensis` ADD CONSTRAINT `presensis_pegawai_id_foreign` 
                        FOREIGN KEY (`pegawai_id`) REFERENCES `pegawais` (`id`) ON DELETE CASCADE');
                }
            }

            // Aktifkan kembali foreign key checks
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        } catch (\Exception $e) {
            // Pastikan untuk mengaktifkan kembali foreign key checks jika terjadi error
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
    }
};
