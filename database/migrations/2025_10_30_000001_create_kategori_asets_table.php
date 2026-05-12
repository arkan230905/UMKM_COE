<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_asets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_aset_id')->constrained('jenis_asets')->onDelete('cascade');
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->integer('umur_ekonomis')->default(1)->comment('Dalam tahun');
            $table->decimal('tarif_penyusutan', 5, 2)->default(0)->comment('Dalam persen');
            $table->timestamps();
        });

        // Insert default data for Aset Tetap
        $tetapId = DB::table('jenis_asets')->where('nama', 'Aset Tetap')->value('id');
        DB::table('kategori_asets')->insert([
            ['jenis_aset_id' => $tetapId, 'kode' => 'AT-01', 'nama' => 'Tanah', 'umur_ekonomis' => 0, 'tarif_penyusutan' => 0],
            ['jenis_aset_id' => $tetapId, 'kode' => 'AT-02', 'nama' => 'Bangunan', 'umur_ekonomis' => 20, 'tarif_penyusutan' => 5],
            ['jenis_aset_id' => $tetapId, 'kode' => 'AT-03', 'nama' => 'Kendaraan', 'umur_ekonomis' => 5, 'tarif_penyusutan' => 20],
            ['jenis_aset_id' => $tetapId, 'kode' => 'AT-04', 'nama' => 'Peralatan Kantor', 'umur_ekonomis' => 4, 'tarif_penyusutan' => 25],
            ['jenis_aset_id' => $tetapId, 'kode' => 'AT-05', 'nama' => 'Peralatan Produksi', 'umur_ekonomis' => 8, 'tarif_penyusutan' => 12.5],
        ]);

        // Insert default data for Aset Tidak Tetap
        $tidakTetapId = DB::table('jenis_asets')->where('nama', 'Aset Tidak Tetap')->value('id');
        DB::table('kategori_asets')->insert([
            ['jenis_aset_id' => $tidakTetapId, 'kode' => 'ATT-01', 'nama' => 'Persediaan Barang', 'umur_ekonomis' => 1, 'tarif_penyusutan' => 100],
            ['jenis_aset_id' => $tidakTetapId, 'kode' => 'ATT-02', 'nama' => 'Kas dan Bank', 'umur_ekonomis' => 0, 'tarif_penyusutan' => 0],
            ['jenis_aset_id' => $tidakTetapId, 'kode' => 'ATT-03', 'nama' => 'Piutang Usaha', 'umur_ekonomis' => 1, 'tarif_penyusutan' => 0],
        ]);

        // Insert default data for Aset Tidak Berwujud
        $tidakBerwujudId = DB::table('jenis_asets')->where('nama', 'Aset Tidak Berwujud')->value('id');
        DB::table('kategori_asets')->insert([
            ['jenis_aset_id' => $tidakBerwujudId, 'kode' => 'ATB-01', 'nama' => 'Hak Cipta', 'umur_ekonomis' => 10, 'tarif_penyusutan' => 10],
            ['jenis_aset_id' => $tidakBerwujudId, 'kode' => 'ATB-02', 'nama' => 'Merek Dagang', 'umur_ekonomis' => 5, 'tarif_penyusutan' => 20],
            ['jenis_aset_id' => $tidakBerwujudId, 'kode' => 'ATB-03', 'nama' => 'Lisensi', 'umur_ekonomis' => 5, 'tarif_penyusutan' => 20],
            ['jenis_aset_id' => $tidakBerwujudId, 'kode' => 'ATB-04', 'nama' => 'Goodwill', 'umur_ekonomis' => 10, 'tarif_penyusutan' => 10],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_asets');
    }
};
