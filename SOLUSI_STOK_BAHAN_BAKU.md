# Solusi Masalah Stok Bahan Baku

## Masalah
Laporan stok menunjukkan **100kg** tapi di detail bahan baku menunjukkan **150kg** untuk item yang sama.

## Penyebab
1. **Detail bahan baku** mengambil stok dari field `stok` di tabel `bahan_bakus` (master data)
2. **Laporan stok** menghitung dari tabel `stock_movements` (real-time calculation)
3. Ada **inkonsistensi** antara master data dan movement data

## Solusi yang Diterapkan

### 1. Perbaikan Controller BiayaBahanController.php
- Menambahkan perhitungan stok real-time dari `stock_movements`
- Menampilkan stok yang konsisten dengan laporan stok
- Menambahkan informasi perbandingan stok master vs real-time

### 2. Method Baru di Model BahanBaku.php
```php
// Sinkronisasi stok master dengan stock movements
public function syncStokWithMovements()

// Get stok real-time (konsisten dengan laporan stok)
public function getStokRealTimeAttribute()

// Cek konsistensi stok
public function isStokConsistent()

// Sinkronisasi semua bahan baku
public static function syncAllStokWithMovements()
```

### 3. Command Artisan untuk Sinkronisasi
```bash
# Sinkronisasi semua bahan baku
php artisan bahan-baku:sync-stok

# Sinkronisasi bahan baku tertentu
php artisan bahan-baku:sync-stok --id=5

# Force sync tanpa konfirmasi
php artisan bahan-baku:sync-stok --force
```

### 4. Script Analisis dan Perbaikan
File `fix_bahan_baku_stok_logic.php` untuk:
- Menganalisis perbedaan stok
- Memperbaiki inkonsistensi
- Sinkronisasi otomatis

## Cara Menggunakan

### 1. Jalankan Analisis
```bash
php fix_bahan_baku_stok_logic.php
```

### 2. Sinkronisasi via Artisan Command
```bash
# Lihat semua inkonsistensi
php artisan bahan-baku:sync-stok

# Perbaiki otomatis
php artisan bahan-baku:sync-stok --force
```

### 3. Sinkronisasi Programmatik
```php
use App\Models\BahanBaku;

// Sinkronisasi satu bahan baku
$bahanBaku = BahanBaku::find(5);
$result = $bahanBaku->syncStokWithMovements();

// Sinkronisasi semua bahan baku
$results = BahanBaku::syncAllStokWithMovements();

// Cek stok real-time
$stokRealTime = $bahanBaku->stok_real_time;

// Cek konsistensi
if (!$bahanBaku->isStokConsistent()) {
    // Lakukan sinkronisasi
    $bahanBaku->syncStokWithMovements();
}
```

## Hasil Setelah Perbaikan

### Sebelum:
- **Detail Bahan Baku**: 150kg (dari field `stok`)
- **Laporan Stok**: 100kg (dari `stock_movements`)
- **Status**: ❌ Inkonsisten

### Sesudah:
- **Detail Bahan Baku**: 100kg (disinkronkan dengan `stock_movements`)
- **Laporan Stok**: 100kg (dari `stock_movements`)
- **Status**: ✅ Konsisten

## Pencegahan Masalah di Masa Depan

### 1. Gunakan Method updateStok()
```php
// BENAR: Gunakan method model
$bahanBaku->updateStok(50, 'in', 'Pembelian baru');

// SALAH: Update langsung field stok
$bahanBaku->stok += 50; // Tidak mencatat stock movement
```

### 2. Pastikan Setiap Transaksi Tercatat
- Pembelian → Stock Movement IN
- Produksi (konsumsi) → Stock Movement OUT
- Penjualan → Stock Movement OUT
- Adjustment → Stock Movement IN/OUT

### 3. Sinkronisasi Berkala
Tambahkan ke scheduler di `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Sinkronisasi stok setiap hari jam 2 pagi
    $schedule->command('bahan-baku:sync-stok --force')
             ->dailyAt('02:00');
}
```

### 4. Validation di Controller
```php
// Sebelum menampilkan data, pastikan konsistensi
if (!$bahanBaku->isStokConsistent()) {
    $bahanBaku->syncStokWithMovements();
}
```

## Monitoring dan Maintenance

### 1. Cek Konsistensi Berkala
```bash
# Cek tanpa melakukan perubahan
php artisan bahan-baku:sync-stok --dry-run
```

### 2. Log Monitoring
Semua operasi stok dicatat di log Laravel untuk tracking.

### 3. Dashboard Monitoring
Bisa ditambahkan widget di dashboard untuk menampilkan:
- Jumlah bahan baku dengan inkonsistensi
- Terakhir sinkronisasi
- Status kesehatan stok

## Kesimpulan

Solusi ini memastikan bahwa:
1. ✅ Detail bahan baku dan laporan stok menunjukkan nilai yang sama
2. ✅ Stok dihitung secara real-time dari stock movements
3. ✅ Inkonsistensi dapat dideteksi dan diperbaiki otomatis
4. ✅ Sistem lebih reliable dan akurat

**Sekarang Ayam Potong akan menunjukkan 100kg di kedua tempat!** 🎯