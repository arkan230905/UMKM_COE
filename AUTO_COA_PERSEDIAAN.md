# Auto COA Persediaan System

## Overview
Sistem otomatis untuk meng-assign COA Persediaan spesifik ke Bahan Baku dan Bahan Pendukung berdasarkan nama item.

## Fitur

### 1. Observer Auto-Assignment
Saat **create bahan baku/pendukung baru**, sistem otomatis set `coa_persediaan_id` berdasarkan nama.

**File:**
- `app/Observers/BahanBakuObserver.php`
- `app/Observers/BahanPendukungObserver.php`

**Mapping Bahan Baku:**
- Ayam Potong → 1141 (Pers. Bahan Baku Ayam Potong)
- Ayam Kampung → 1142 (Pers. Bahan Baku Ayam Kampung)  
- Bebek → 1143 (Pers. Bahan Baku Bebek)
- Ayam lainnya → 1144 (Pers. Bahan Baku Ayam Lainnya)
- Default → 114 (Pers. Bahan Baku)

**Mapping Bahan Pendukung:**
- Air → 1150 (Pers. Bahan Pendukung Air)
- Minyak → 1151 (Pers. Bahan Pendukung Minyak Goreng)
- Tepung Terigu → 1152 (Pers. Bahan Pendukung Tepung Terigu)
- Maizena → 1153 (Pers. Bahan Pendukung Tepung Maizena)
- Lada → 1154 (Pers. Bahan Pendukung Lada)
- Kaldu → 1155 (Pers. Bahan Pendukung Bubuk Kaldu)
- Bawang Putih → 1156 (Pers. Bahan Pendukung Bubuk Bawang Putih)
- Kemasan → 1157 (Pers. Bahan Pendukung Kemasan)
- Default → 115 (Pers. Bahan Pendukung)

### 2. Jurnal Pembelian
Saat **create/update pembelian**, jurnal otomatis menggunakan COA spesifik dari `coa_persediaan_id`.

**File:** 
- `app/Services/PembelianJournalService.php`
- `app/Http/Controllers/PembelianController.php`

**Logika:**
1. Eager load relasi `bahanBaku` dan `bahanPendukung`
2. Ambil `coa_persediaan_id` dari item yang dibeli
3. Query COA berdasarkan `kode_akun` dan `user_id`
4. Error jika COA tidak ditemukan - user harus set di master data
5. Jika beberapa item pakai COA sama, otomatis digabung

**Contoh Jurnal:**
```
Pembelian Ayam Potong Rp 2.000.000
Debit:
- Pers. Bahan Baku Ayam Potong (1141) Rp 2.000.000
Kredit:
- Hutang Usaha (210) Rp 2.000.000
```

### 3. Command untuk Update Data Lama
Untuk **user yang sudah ada** dengan data bahan baku/pendukung yang belum punya COA.

**Command:**
```bash
php artisan coa:update-persediaan-all
```

**Fungsi:**
- Scan semua user
- Update `coa_persediaan_id` untuk semua bahan baku/pendukung
- Gunakan mapping yang sama dengan observer
- Fallback ke generic COA jika specific tidak ada

## Cara Penggunaan

### Untuk User Baru
✅ **Otomatis** - saat create bahan baku/pendukung, observer langsung set COA

### Untuk User Lama
Jalankan command sekali:
```bash
php artisan coa:update-persediaan-all
```

### Menambah Mapping Baru
Edit function `getCoaKodeFromName()` di:
- `app/Observers/BahanBakuObserver.php`
- `app/Observers/BahanPendukungObserver.php`
- `app/Console/Commands/UpdateCoaPersediaanForAllUsers.php`

## Testing

### Test Observer
```php
// Buat bahan baku baru
$bb = BahanBaku::create([
    'nama_bahan' => 'Ayam Kampung',
    'user_id' => 3,
    ...
]);

// Check auto-assigned COA
echo $bb->coa_persediaan_id; // Should be: 1142
```

### Test Jurnal Pembelian
1. Buat pembelian dengan item yang punya `coa_persediaan_id`
2. Cek jurnal yang ter-generate
3. Pastikan menggunakan COA spesifik, bukan generic

### Test Command
```bash
# Dry run - lihat apa yang akan diupdate
php artisan coa:update-persediaan-all --dry-run

# Execute update
php artisan coa:update-persediaan-all
```

## Troubleshooting

### Error: "Bahan baku 'XXX' belum memiliki COA Persediaan"
**Penyebab:** COA tidak ditemukan untuk user tersebut

**Solusi:**
1. Cek di master data COA, pastikan ada COA dengan kode yang sesuai
2. Pastikan COA punya `user_id` yang benar
3. Atau set `coa_persediaan_id` manual di master data bahan baku/pendukung

### COA Tidak Auto-Assign
**Penyebab:** Observer tidak terdaftar atau nama tidak match pattern

**Solusi:**
1. Pastikan observer terdaftar di `AppServiceProvider`
2. Check pattern matching di `getCoaKodeFromName()`
3. Set manual di master data atau jalankan command update

### Jurnal Pakai COA Generic
**Penyebab:** `coa_persediaan_id` masih NULL atau tidak valid

**Solusi:**
1. Jalankan `php artisan coa:update-persediaan-all`
2. Atau update manual di master data bahan baku/pendukung

## Referensi
- COA Structure: `database/seeders/DefaultCoaSeeder.php`
- Pembelian Flow: `app/Http/Controllers/PembelianController.php`
- Journal Service: `app/Services/PembelianJournalService.php`
