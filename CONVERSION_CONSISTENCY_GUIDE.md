# Panduan Konsistensi Konversi Satuan

## Overview
Sistem ini memastikan konsistensi konversi satuan untuk laporan stok di seluruh aplikasi. Semua bahan baku, bahan pendukung, dan produk akan menggunakan logika konversi yang sama.

## Prinsip Konsistensi

### 1. Prioritas Field Konversi
Sistem menggunakan prioritas berikut untuk konversi:
1. **`sub_satuan_X_konversi`** (field utama) - digunakan untuk laporan stok
2. **`sub_satuan_X_nilai`** (field cadangan) - disinkronkan ke konversi jika konversi kosong
3. **1.0** (fallback) - jika kedua field kosong

### 2. Auto-Sync Mechanism
- Saat data baru dibuat/diupdate, sistem otomatis menyinkronkan `nilai` → `konversi`
- Observer `ConversionConsistencyObserver` menangani sinkronisasi otomatis
- Command `conversion:ensure-consistency` untuk sinkronisasi manual

## Struktur Konversi

### Bahan Baku (BahanBaku)
```
Satuan Utama: Ekor/Kilogram/dll
├── Sub Satuan 1: sub_satuan_1_konversi (contoh: 6 Potong per Ekor)
├── Sub Satuan 2: sub_satuan_2_konversi (contoh: 1.5 Kilogram per Ekor)  
└── Sub Satuan 3: sub_satuan_3_konversi (contoh: 1500 Gram per Ekor)
```

### Bahan Pendukung (BahanPendukung)
```
Satuan Utama: Liter/Kilogram/dll
├── Sub Satuan 1: sub_satuan_1_konversi
├── Sub Satuan 2: sub_satuan_2_konversi
└── Sub Satuan 3: sub_satuan_3_konversi
```

### Produk (Produk)
```
Satuan Utama: Pieces/Kilogram/dll
├── Sub Satuan 1: sub_satuan_1_konversi
├── Sub Satuan 2: sub_satuan_2_konversi
└── Sub Satuan 3: sub_satuan_3_konversi
```

## Contoh Konversi

### Ayam Kampung (Ekor)
- 1 Ekor = 6 Potong
- 1 Ekor = 1.5 Kilogram
- 1 Ekor = 1500 Gram

### Bebek (Ekor)
- 1 Ekor = 6 Potong
- 1 Ekor = 1.5 Kilogram
- 1 Ekor = 1500 Gram

### Ayam Potong (Kilogram)
- 1 Kilogram = 1000 Gram
- 1 Kilogram = 4 Potong
- 1 Kilogram = 10 Ons

## Implementasi Teknis

### 1. LaporanController Helper Methods
```php
// Mendapatkan item dengan relasi lengkap
private function getItemWithConversions($tipe, $itemId)

// Mendapatkan satuan yang tersedia dengan konversi konsisten
private function getAvailableSatuans($item, $tipe)

// Mendapatkan faktor konversi yang konsisten
private function getConsistentConversionFactor($item, $subSatuanNumber)

// Memastikan konsistensi konversi (static method)
public static function ensureConversionConsistency($item)
```

### 2. ConversionConsistencyObserver
- Otomatis dipicu saat model `created` atau `updated`
- Menyinkronkan `nilai` → `konversi` jika diperlukan
- Menggunakan `saveQuietly()` untuk menghindari loop observer

### 3. EnsureConversionConsistency Command
```bash
# Lihat apa yang akan diubah tanpa mengubah data
php artisan conversion:ensure-consistency --dry-run

# Terapkan perubahan
php artisan conversion:ensure-consistency
```

## Cara Menambah Data Baru

### 1. Melalui Form/Interface
- Isi field `sub_satuan_X_nilai` dengan nilai konversi yang benar
- Observer akan otomatis menyinkronkan ke `sub_satuan_X_konversi`

### 2. Melalui Seeder/Migration
```php
BahanBaku::create([
    'nama_bahan' => 'Ayam Baru',
    'satuan_id' => 7, // Ekor
    'sub_satuan_1_id' => 6, // Potong
    'sub_satuan_1_nilai' => 6.0, // 1 Ekor = 6 Potong
    'sub_satuan_1_konversi' => 6.0, // Eksplisit set konversi
    // ... field lainnya
]);
```

### 3. Melalui Import/API
- Pastikan field `sub_satuan_X_konversi` diisi dengan benar
- Atau isi `sub_satuan_X_nilai` dan jalankan command konsistensi

## Troubleshooting

### 1. Konversi Tidak Muncul di Laporan Stok
```bash
# Periksa konsistensi data
php artisan conversion:ensure-consistency --dry-run

# Perbaiki jika ada masalah
php artisan conversion:ensure-consistency
```

### 2. Data Lama Tidak Konsisten
```bash
# Sinkronkan semua data
php artisan conversion:ensure-consistency
```

### 3. Konversi Salah di Laporan
- Periksa field `sub_satuan_X_konversi` di database
- Pastikan nilai sesuai dengan master data
- Update manual jika diperlukan

## Best Practices

### 1. Untuk Developer
- Selalu gunakan helper methods di `LaporanController`
- Jangan hardcode konversi di view atau controller lain
- Test konversi setelah menambah data baru

### 2. Untuk Data Entry
- Pastikan konversi satuan sesuai dengan realitas
- Gunakan satuan yang konsisten (contoh: selalu Gram, bukan gr/GR/gram)
- Verifikasi hasil konversi di laporan stok

### 3. Untuk Maintenance
- Jalankan command konsistensi secara berkala
- Monitor log error untuk masalah konversi
- Backup data sebelum perubahan besar

## Testing

### 1. Test Manual
```bash
# Buat data baru dengan nilai konversi
# Periksa apakah muncul di laporan stok dengan benar
# Verifikasi semua satuan menampilkan konversi yang tepat
```

### 2. Test Otomatis
```php
// Test di feature test
$this->assertDatabaseHas('bahan_bakus', [
    'id' => $bahanBaku->id,
    'sub_satuan_1_konversi' => $expectedConversion
]);
```

## Changelog

### v1.0 (Current)
- ✅ Implementasi helper methods di LaporanController
- ✅ Observer untuk auto-sync konversi
- ✅ Command untuk konsistensi manual
- ✅ Dokumentasi lengkap
- ✅ Support untuk BahanBaku, BahanPendukung, Produk

### Future Enhancements
- [ ] Unit tests untuk semua konversi
- [ ] API endpoint untuk validasi konversi
- [ ] Dashboard monitoring konsistensi data
- [ ] Export/import dengan validasi konversi