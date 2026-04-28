<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking jabatans table...\n";
    
    if (\Schema::hasTable('jabatans')) {
        echo "✅ Table 'jabatans' exists\n";
        
        $jabatanCount = \DB::table('jabatans')->count();
        echo "- Total jabatans records: {$jabatanCount}\n";
    } else {
        echo "❌ Table 'jabatans' does not exist\n";
        
        // Create jabatans table first
        echo "Creating jabatans table...\n";
        \Schema::create('jabatans', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan');
            $table->text('deskripsi')->nullable();
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tunjangan', 15, 2)->default(0);
            $table->decimal('uang_makan', 15, 2)->default(0);
            $table->decimal('uang_transport', 15, 2)->default(0);
            $table->decimal('tarif_lembur_per_jam', 15, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        echo "✅ Jabatans table created\n";
    }
    
    // Now create pegawais table without foreign key constraint first
    echo "\nCreating pegawais table...\n";
    
    if (\Schema::hasTable('pegawais')) {
        \Schema::dropIfExists('pegawais');
        echo "Dropped existing pegawais table\n";
    }
    
    \Schema::create('pegawais', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->string('kode_pegawai')->unique();
        $table->string('nama');
        $table->string('email')->unique()->nullable();
        $table->string('no_telepon')->nullable();
        $table->string('alamat')->nullable();
        $table->date('tanggal_lahir')->nullable();
        $table->string('jenis_kelamin')->nullable();
        $table->string('agama')->nullable();
        $table->string('status_perkawinan')->nullable();
        $table->integer('jumlah_anak')->default(0);
        $table->string('pendidikan_terakhir')->nullable();
        $table->string('jurusan')->nullable();
        $table->string('keahlian')->nullable();
        $table->string('pengalaman_kerja')->nullable();
        $table->date('tanggal_masuk')->nullable();
        $table->string('status_karyawan')->default('aktif');
        $table->string('jabatan')->nullable();
        $table->decimal('gaji_pokok', 15, 2)->default(0);
        $table->decimal('tunjangan', 15, 2)->default(0);
        $table->decimal('uang_makan', 15, 2)->default(0);
        $table->decimal('uang_transport', 15, 2)->default(0);
        $table->decimal('tarif_lembur_per_jam', 15, 2)->default(0);
        $table->string('bank')->nullable();
        $table->string('nomor_rekening')->nullable();
        $table->string('atas_nama')->nullable();
        $table->string('npwp')->nullable();
        $table->string('bpjs_kesehatan')->nullable();
        $table->string('bpjs_ketenagakerjaan')->nullable();
        $table->text('keterangan')->nullable();
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
        $table->foreignId('jabatan_id')->nullable(); // Remove constraint for now
        $table->timestamps();
    });
    
    echo "✅ Pegawais table created successfully\n";
    
    // Verify table exists
    if (\Schema::hasTable('pegawais')) {
        echo "✅ Table pegawais EXISTS\n";
        
        $pegawaiCount = \DB::table('pegawais')->count();
        echo "- Total pegawais records: {$pegawaiCount}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
