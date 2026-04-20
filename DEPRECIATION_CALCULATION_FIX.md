# Perbaikan Perhitungan Penyusutan - Metode Sum of Years Digits & Double Declining Balance

## Masalah yang Diperbaiki

User melaporkan bahwa **nominal per bulan untuk penyusutan Sum of Years Digits dan Double Declining Balance masih salah**.

## Root Cause Analysis

Sebelumnya, sistem menggunakan perhitungan yang sama (rata-rata) untuk semua metode penyusutan:
```php
// SALAH - Semua metode menggunakan rata-rata
$penyusutanPerBulan = $nilaiDisusutkan / ($umurManfaat * 12);
```

Padahal setiap metode memiliki formula yang berbeda dan nilai penyusutan yang berubah setiap periode.

## Formula Penyusutan yang Benar

### 1. **Garis Lurus (Straight Line)**
- **Formula**: `(Harga Perolehan - Nilai Residu) / Umur Manfaat / 12`
- **Karakteristik**: Penyusutan **TETAP** setiap bulan
- **Contoh**: Rp 80.000.000 / 5 tahun / 12 bulan = **Rp 1.333.333 per bulan**

### 2. **Saldo Menurun Ganda (Double Declining Balance)**
- **Formula**: `Nilai Buku Saat Ini × (2 / Umur Manfaat) / 12`
- **Karakteristik**: Penyusutan **MENURUN** setiap bulan
- **Rate**: 2 / 5 tahun = 40% per tahun = 3.33% per bulan
- **Contoh**:
  - Bulan 1: Rp 85.000.000 × 3.33% = **Rp 2.833.333**
  - Bulan 2: Rp 82.166.667 × 3.33% = **Rp 2.738.889**
  - Bulan 3: Rp 79.427.778 × 3.33% = **Rp 2.647.593**
  - Dan seterusnya (menurun terus)

### 3. **Jumlah Angka Tahun (Sum of Years Digits)**
- **Formula**: `(Nilai Disusutkan × Sisa Umur) / Sum of Years / 12`
- **Karakteristik**: Penyusutan **MENURUN** setiap tahun
- **Sum of Years**: (5 × 6) / 2 = 15
- **Contoh**:
  - Tahun 1: (Rp 80.000.000 × 5) / 15 / 12 = **Rp 2.222.222 per bulan**
  - Tahun 2: (Rp 80.000.000 × 4) / 15 / 12 = **Rp 1.777.778 per bulan**
  - Tahun 3: (Rp 80.000.000 × 3) / 15 / 12 = **Rp 1.333.333 per bulan**
  - Tahun 4: (Rp 80.000.000 × 2) / 15 / 12 = **Rp 888.889 per bulan**
  - Tahun 5: (Rp 80.000.000 × 1) / 15 / 12 = **Rp 444.444 per bulan**

## Implementasi Perbaikan

### 1. **Model Aset - Method Baru**
**File**: `app/Models/Aset.php`

#### Method `hitungPenyusutanPerBulanSaatIni()`
```php
public function hitungPenyusutanPerBulanSaatIni(): float
{
    // Menghitung penyusutan per bulan berdasarkan:
    // - Metode penyusutan yang digunakan
    // - Bulan/tahun ke berapa dalam siklus penyusutan
    // - Nilai buku saat ini (untuk saldo menurun)
    // - Sisa umur (untuk sum of years digits)
}
```

#### Method `hitungAkumulasiPenyusutanSaatIni()` - Diperbaiki
```php
public function hitungAkumulasiPenyusutanSaatIni(): float
{
    // Menghitung akumulasi dengan metode yang benar:
    // - Garis lurus: rata-rata × bulan
    // - Saldo menurun: iterasi per bulan dengan nilai buku menurun
    // - Sum of years: iterasi per tahun dengan sisa umur menurun
}
```

### 2. **AsetController - Update Logic**
**File**: `app/Http/Controllers/AsetController.php`

#### Method `index()` dan `show()` - Diperbaiki
- Menggunakan `hitungPenyusutanPerBulanSaatIni()` untuk perhitungan yang akurat
- Menampilkan penyusutan sesuai periode saat ini
- Logging detail untuk debugging

### 3. **DepreciationCalculationService - Diperbaiki**
**File**: `app/Services/DepreciationCalculationService.php`

#### Method `calculateCurrentMonthDepreciation()` - Diperbaiki
- Menghitung penyusutan berdasarkan bulan/tahun ke berapa
- Menggunakan nilai buku saat ini untuk saldo menurun
- Menggunakan sisa umur untuk sum of years digits

## Contoh Perhitungan Real

### Data Aset Contoh:
- **Harga Perolehan**: Rp 85.000.000
- **Nilai Residu**: Rp 5.000.000
- **Nilai Disusutkan**: Rp 80.000.000
- **Umur Manfaat**: 5 tahun

### Hasil Perhitungan:

#### **Garis Lurus**
```
Penyusutan per bulan: Rp 1.333.333 (TETAP)
```

#### **Saldo Menurun Ganda**
```
Tahun 1: Rp 2.833.333 per bulan (rata-rata)
Tahun 2: Rp 1.700.000 per bulan (rata-rata)
Tahun 3: Rp 1.020.000 per bulan (rata-rata)
(Menurun setiap bulan berdasarkan nilai buku)
```

#### **Jumlah Angka Tahun**
```
Tahun 1: Rp 2.222.222 per bulan
Tahun 2: Rp 1.777.778 per bulan
Tahun 3: Rp 1.333.333 per bulan
Tahun 4: Rp 888.889 per bulan
Tahun 5: Rp 444.444 per bulan
```

## Script untuk Testing dan Perbaikan

### 1. **Test Perhitungan**
```bash
php test_depreciation_calculation.php
```
Menampilkan contoh perhitungan untuk ketiga metode dengan data yang sama.

### 2. **Perbaikan Semua Aset**
```bash
php fix_depreciation_methods.php
```
Memperbarui semua aset dengan perhitungan penyusutan yang benar.

## Validasi Hasil

### ✅ **Sebelum Perbaikan**
- Semua metode: Rp 1.333.333 per bulan (SALAH)

### ✅ **Setelah Perbaikan**
- **Garis Lurus**: Rp 1.333.333 per bulan (TETAP)
- **Saldo Menurun**: Rp 2.833.333 → menurun setiap bulan
- **Sum of Years**: Rp 2.222.222 → menurun setiap tahun

## Monitoring dan Verifikasi

### 1. **Cek Perhitungan Manual**
```php
$aset = Aset::find(1);
$penyusutanBulanIni = $aset->hitungPenyusutanPerBulanSaatIni();
echo "Penyusutan bulan ini: Rp " . number_format($penyusutanBulanIni, 2);
```

### 2. **Lihat Log Detail**
```bash
tail -f storage/logs/laravel.log | grep "SHOW PAGE - Aset"
```

### 3. **Bandingkan dengan Manual**
- Buka halaman detail aset
- Bandingkan dengan perhitungan manual menggunakan formula di atas

## Files yang Dimodifikasi

1. `app/Models/Aset.php` - Method perhitungan penyusutan yang benar
2. `app/Http/Controllers/AsetController.php` - Logic update untuk menggunakan method baru
3. `app/Services/DepreciationCalculationService.php` - Service perhitungan yang diperbaiki
4. `test_depreciation_calculation.php` - Script test perhitungan
5. `fix_depreciation_methods.php` - Script perbaikan batch
6. `DEPRECIATION_CALCULATION_FIX.md` - Dokumentasi ini

## Status: ✅ COMPLETED

Sekarang sistem menghitung penyusutan dengan benar:
- **Garis Lurus**: Tetap setiap bulan
- **Saldo Menurun**: Menurun setiap bulan berdasarkan nilai buku
- **Sum of Years**: Menurun setiap tahun berdasarkan sisa umur

Nominal penyusutan per bulan sudah **AKURAT** sesuai dengan metode yang dipilih! 🎉

---
**Completed**: April 20, 2026  
**Task Status**: FULLY RESOLVED with correct depreciation formulas