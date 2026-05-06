# Setup Sistem Presensi dan Penggajian

## 📋 Checklist Setup

### 1. Database Migration
```bash
# Jalankan migration untuk membuat tabel baru
php artisan migrate

# Tabel yang dibuat:
# - kalender_kerja
# - rekap_presensi_bulanan
# - Modifikasi: presensis (tambah periode_bulan, periode_tahun)
# - Modifikasi: penggajians (tambah periode_bulan, periode_tahun, total_hari_hadir, total_alpha, total_jam)
```

### 2. Model Files
Pastikan file-file berikut sudah ada:
- ✅ `app/Models/Presensi.php` (updated)
- ✅ `app/Models/Penggajian.php` (updated)
- ✅ `app/Models/KalenderKerja.php` (new)
- ✅ `app/Models/RekapPresensiBulanan.php` (new)

### 3. Service Files
- ✅ `app/Services/PenggajianService.php` (new)

### 4. Controller Files
- ✅ `app/Http/Controllers/PresensiController.php` (new)
- ✅ `app/Http/Controllers/PenggajianController.php` (new)

### 5. View Files
- ✅ `resources/views/transaksi/presensi/index.blade.php`
- ✅ `resources/views/transaksi/presensi/create.blade.php`
- ✅ `resources/views/transaksi/penggajian/index.blade.php`
- ✅ `resources/views/transaksi/penggajian/generate-form.blade.php`
- ✅ `resources/views/transaksi/penggajian/show.blade.php`
- ✅ `resources/views/transaksi/penggajian/slip.blade.php`

### 6. Routes
Tambahkan ke `routes/web.php`:
```php
// Presensi Routes
Route::prefix('transaksi/presensi')->name('presensi.')->group(function () {
    Route::get('/', [PresensiController::class, 'index'])->name('index');
    Route::get('/create', [PresensiController::class, 'create'])->name('create');
    Route::post('/', [PresensiController::class, 'store'])->name('store');
    Route::get('/{id}', [PresensiController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [PresensiController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PresensiController::class, 'update'])->name('update');
    Route::delete('/{id}', [PresensiController::class, 'destroy'])->name('destroy');
    Route::get('/rekap/{pegawaiId}/{bulan}/{tahun}', [PresensiController::class, 'getRekapBulanan'])->name('rekap');
});

// Penggajian Routes
Route::prefix('transaksi/penggajian')->name('penggajian.')->group(function () {
    Route::get('/', [PenggajianController::class, 'index'])->name('index');
    Route::get('/generate', [PenggajianController::class, 'generateForm'])->name('generate-form');
    Route::post('/generate', [PenggajianController::class, 'generate'])->name('generate');
    Route::get('/{id}', [PenggajianController::class, 'show'])->name('show');
    Route::post('/{id}/mark-as-paid', [PenggajianController::class, 'markAsPaid'])->name('mark-as-paid');
    Route::get('/{id}/print-slip', [PenggajianController::class, 'printSlip'])->name('print-slip');
    Route::get('/summary', [PenggajianController::class, 'summary'])->name('summary');
    Route::get('/export', [PenggajianController::class, 'export'])->name('export');
});
```

---

## 🔧 Konfigurasi Awal

### 1. Setup Kalender Kerja
Buat kalender kerja untuk bulan-bulan yang akan datang:

```php
// Bisa dilakukan via Tinker atau Seeder
php artisan tinker

// Buat kalender kerja untuk April 2026
KalenderKerja::create([
    'bulan' => 4,
    'tahun' => 2026,
    'target_hari_kerja' => 26,
    'keterangan' => 'April 2026'
]);

// Buat untuk bulan-bulan lainnya
for ($bulan = 1; $bulan <= 12; $bulan++) {
    KalenderKerja::firstOrCreate(
        ['bulan' => $bulan, 'tahun' => 2026],
        ['target_hari_kerja' => 26]
    );
}
```

### 2. Verifikasi Tarif Per Jam
Pastikan setiap jabatan memiliki `tarif_btkl`:

```php
// Cek di database
SELECT id, nama_jabatan, tarif_btkl FROM jabatans;

// Jika kosong, update:
UPDATE jabatans SET tarif_btkl = 50000 WHERE nama_jabatan = 'Operator Produksi';
UPDATE jabatans SET tarif_btkl = 60000 WHERE nama_jabatan = 'Supervisor';
```

### 3. Verifikasi COA Kas/Bank
Pastikan COA untuk kas/bank sudah ada:

```php
// Cek di database
SELECT id, kode_akun, nama_akun FROM coas WHERE kode_akun LIKE '10%';

// Default COA Kas: 101
// Bisa di-customize di PenggajianService::createPenggajian()
```

---

## 📊 Testing Data

### 1. Input Presensi Test
```php
// Buat presensi test untuk April 2026
$pegawai = Pegawai::first();

for ($hari = 1; $hari <= 26; $hari++) {
    Presensi::create([
        'pegawai_id' => $pegawai->id,
        'tgl_presensi' => Carbon::createFromDate(2026, 4, $hari),
        'jam_masuk' => '08:00',
        'jam_keluar' => '17:00',
        'keterangan' => 'Test data'
    ]);
}
```

### 2. Generate Penggajian Test
```php
// Via Controller
POST /transaksi/penggajian/generate
{
    "bulan": 4,
    "tahun": 2026,
    "tanggal_penggajian": "2026-04-30"
}
```

### 3. Verifikasi Hasil
```php
// Cek rekap presensi
$rekap = RekapPresensiBulanan::where('pegawai_id', $pegawai->id)
    ->where('periode_bulan', 4)
    ->where('periode_tahun', 2026)
    ->first();

// Cek penggajian
$penggajian = Penggajian::where('pegawai_id', $pegawai->id)
    ->where('periode_bulan', 4)
    ->where('periode_tahun', 2026)
    ->first();
```

---

## 🚀 Deployment Checklist

### Pre-Deployment
- [ ] Backup database
- [ ] Test migration di staging
- [ ] Test semua controller methods
- [ ] Test semua views
- [ ] Verifikasi routes
- [ ] Test unique constraint (coba generate 2x untuk periode sama)

### Deployment
```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install

# 3. Run migrations
php artisan migrate

# 4. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Restart queue (jika ada)
php artisan queue:restart
```

### Post-Deployment
- [ ] Verifikasi tabel baru di database
- [ ] Test input presensi
- [ ] Test generate penggajian
- [ ] Test print slip gaji
- [ ] Monitor error logs

---

## 🐛 Troubleshooting

### Error: "SQLSTATE[42S02]: Table or view not found"
**Solusi**: Jalankan migration
```bash
php artisan migrate
```

### Error: "Call to undefined method"
**Solusi**: Pastikan model dan controller sudah di-import dengan benar
```php
use App\Models\Presensi;
use App\Models\Penggajian;
use App\Services\PenggajianService;
```

### Error: "Unique constraint violation"
**Solusi**: Penggajian untuk periode ini sudah ada. Gunakan update atau hapus yang lama
```php
// Hapus penggajian lama
Penggajian::where('pegawai_id', $pegawaiId)
    ->where('periode_bulan', $bulan)
    ->where('periode_tahun', $tahun)
    ->delete();

// Atau update
Penggajian::where('pegawai_id', $pegawaiId)
    ->where('periode_bulan', $bulan)
    ->where('periode_tahun', $tahun)
    ->update([...]);
```

### Error: "Tarif per jam is null"
**Solusi**: Pastikan pegawai memiliki jabatan dengan tarif_btkl
```php
// Cek
$pegawai = Pegawai::with('jabatan')->find($pegawaiId);
dd($pegawai->jabatan->tarif_btkl);

// Update jika kosong
$pegawai->jabatan->update(['tarif_btkl' => 50000]);
```

---

## 📞 Support

Jika ada pertanyaan atau masalah:
1. Cek dokumentasi: `SISTEM_PRESENSI_PENGGAJIAN_BTKL.md`
2. Cek error logs: `storage/logs/laravel.log`
3. Cek database: Verifikasi struktur tabel dan data
4. Test dengan Tinker: `php artisan tinker`

---

**Setup Date**: 30 April 2026  
**Version**: 1.0  
**Status**: Ready for Deployment
