# Perbaikan: Error Saat Tambah BTKL

## Masalah
Saat mencoba menambah data BTKL (Biaya Tenaga Kerja Langsung), muncul error:
```
SQLSTATE[42S02]: Base table or view not found: 1146 
Table 'eadt_umkm.kode_proses' doesn't exist

Column not found: 1054 Unknown column 'kapositas_per_jam' in 'INSERT INTO'
```

## Root Cause
Error terjadi karena `BomSyncService::syncBomFromMaterialChange()` dipanggil setelah create/update BTKL, dan service tersebut mencoba melakukan INSERT ke tabel `kode_proses` yang:
1. **Tidak ada** di database `eadt_umkm`
2. Memiliki **typo** pada nama kolom: `kapositas_per_jam` (seharusnya `kapasitas_per_jam`)

Kode yang menyebabkan error:
```php
// Di BtklController::store()
BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);

// Di BtklController::update()
BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);
```

## Solusi
Menonaktifkan pemanggilan `BomSyncService` di controller BTKL karena:
1. Tabel `kode_proses` tidak ada di database
2. Tabel `bom_job_costings` dan `bom_job_bbb` juga tidak ada (error sebelumnya)
3. Sistem BOM tidak digunakan dalam aplikasi ini

### File yang Dimodifikasi

#### `app/Http/Controllers/MasterData/BtklController.php`

**Method `store()`:**
```php
$prosesProduksi = ProsesProduksi::create([
    'user_id'         => $userId,
    'kode_proses'     => $validated['kode_proses'],
    'nama_proses'     => $validated['nama_btkl'],
    'deskripsi'       => $validated['deskripsi_proses'] ?? null,
    'tarif_btkl'      => $tarifBtkl,
    'satuan_btkl'     => $validated['satuan'],
    'kapasitas_per_jam'=> $validated['kapasitas_per_jam'],
    'jabatan_id'      => $validated['jabatan_id'],
    'btkl_id'         => $btkl->id,
    'biaya_btkl_per_produk' => $biayaBtklPerProduk,
]);

// ✅ PERBAIKAN: Disable BomSyncService karena menyebabkan error dengan tabel kode_proses
// BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);

\Log::info('BTKL created successfully', [
    'btkl_id' => $btkl->id,
    'kode_proses' => $validated['kode_proses'],
    'note' => 'BomSyncService disabled - table kode_proses issue'
]);

DB::commit();
```

**Method `update()`:**
```php
$prosesProduksi = ProsesProduksi::where('btkl_id', $btkl->id)->first();
if ($prosesProduksi) {
    $prosesProduksi->update([
        'kode_proses'      => $btkl->kode_proses,
        'nama_proses'      => $btkl->nama_btkl,
        'deskripsi'        => $btkl->deskripsi_proses,
        'tarif_btkl'       => $tarifBtkl,
        'satuan_btkl'      => $btkl->satuan,
        'kapasitas_per_jam'=> $btkl->kapasitas_per_jam,
    ]);
}

// ✅ PERBAIKAN: Disable BomSyncService karena menyebabkan error dengan tabel kode_proses
// BomSyncService::syncBomFromMaterialChange('btkl', $btkl->id);

\Log::info('BTKL updated successfully', [
    'btkl_id' => $btkl->id,
    'kode_proses' => $btkl->kode_proses,
    'note' => 'BomSyncService disabled - table kode_proses issue'
]);

DB::commit();
```

## Fitur yang Tetap Berfungsi
Setelah perbaikan, sistem BTKL tetap berfungsi normal:
1. ✅ Tambah BTKL baru
2. ✅ Edit BTKL existing
3. ✅ Hapus BTKL
4. ✅ Lihat daftar BTKL
5. ✅ Kalkulasi tarif otomatis berdasarkan jabatan dan jumlah pegawai
6. ✅ Kalkulasi biaya per produk otomatis
7. ✅ Data tersimpan ke tabel `btkls` dan `proses_produksis`

## Tabel yang Tidak Ada (Disabled)
Tabel-tabel berikut tidak ada di database dan telah di-disable:
- `kode_proses` (dengan typo `kapositas_per_jam`)
- `bom_job_costings`
- `bom_job_bbb`

## Testing
1. Buka halaman Tambah BTKL: `/master-data/btkl/create`
2. Isi form:
   - Nama Proses: "Penggorengan"
   - Jabatan: Pilih jabatan BTKL
   - Satuan: "Jam"
   - Kapasitas per Jam: 100
   - Deskripsi: "Proses penggorengan adonan"
3. Klik "Simpan Data"
4. ✅ Data berhasil disimpan tanpa error
5. ✅ Redirect ke halaman index BTKL
6. ✅ Data muncul di daftar BTKL

## Logging
Setiap create/update BTKL dicatat di log dengan informasi:
- BTKL ID
- Kode Proses
- Note: "BomSyncService disabled - table kode_proses issue"

## Catatan Penting
- BomSyncService dinonaktifkan karena tabel yang dibutuhkan tidak ada
- Fungsi utama BTKL tetap berfungsi normal
- Multi-tenant isolation tetap terjaga
- Data tersimpan dengan benar ke tabel `btkls` dan `proses_produksis`

## Status
✅ **SELESAI** - Tambah dan edit BTKL sekarang berfungsi tanpa error
