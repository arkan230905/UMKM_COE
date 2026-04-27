<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Creating pegawais table with basic structure...\n";
    
    // Drop table if exists
    if (\Schema::hasTable('pegawais')) {
        \Schema::dropIfExists('pegawais');
        echo "Dropped existing pegawais table\n";
    }
    
    // Create the table with basic structure
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
        $table->bigInteger('user_id')->nullable();
        $table->bigInteger('jabatan_id')->nullable();
        $table->timestamps();
        
        // Add indexes
        $table->index('user_id');
        $table->index('jabatan_id');
    });
    
    echo "✅ Pegawais table created successfully\n";
    
    // Verify table exists
    if (\Schema::hasTable('pegawais')) {
        echo "✅ Table pegawais EXISTS\n";
        
        // Show table structure
        $columns = \DB::select("DESCRIBE pegawais");
        echo "\n📋 Pegawais Table Columns:\n";
        foreach ($columns as $column) {
            echo "- {$column->Field} ({$column->Type})\n";
        }
        
        // Add to migrations table
        \DB::table('migrations')->insert([
            'migration' => '2025_10_23_012557_create_pegawais_table',
            'batch' => 1004
        ]);
        
        echo "\n✅ Migration tracked\n";
        
        // Test insert a sample record
        \DB::table('pegawais')->insert([
            'kode_pegawai' => 'PG001',
            'nama' => 'Test Employee',
            'user_id' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "✅ Sample record inserted\n";
        
        $pegawaiCount = \DB::table('pegawais')->count();
        echo "- Total pegawais records: {$pegawaiCount}\n";
        
    } else {
        echo "❌ Table pegawais still MISSING\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
