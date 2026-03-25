# Sistem Konversi Bertingkat - Contoh Penggunaan

## Konsep Dasar
Sistem ini menampilkan rumus perhitungan konversi bertingkat dari:
**Satuan Pembelian → Satuan Utama → Sub Satuan**

## Contoh Kasus: Pembelian Ayam

### Input Pembelian:
- **Pembelian**: 10 Kilogram ayam
- **Konversi ke Satuan Utama**: 8 Ekor (manual input)
- **Konversi ke Sub Satuan**: 1 Ekor = 4 Potong (manual input)

### Perhitungan Sistem:
1. **Satuan Pembelian**: 10 Kilogram
2. **Konversi ke Satuan Utama**: 10 KG → 8 Ekor (faktor: 0.8 Ekor/KG)
3. **Konversi ke Sub Satuan**: 8 Ekor → 32 Potong

### Rumus yang Ditampilkan:
```
Rumus: 8 Ekor × 4 Potong/Ekor = 32 Potong
```

## Fitur Sistem:

### 1. Form Pembelian (create.blade.php)
- Menampilkan rumus secara real-time saat user menginput nilai
- Rumus berubah otomatis ketika nilai satuan utama atau sub satuan diubah
- Format: `[Jumlah Satuan Utama] × [Faktor Konversi] [Sub Satuan]/[Satuan Utama] = [Hasil] [Sub Satuan]`

### 2. Detail Pembelian (show.blade.php)
- Menampilkan rumus perhitungan yang sudah tersimpan
- Menunjukkan faktor konversi yang digunakan
- Format yang sama dengan form pembelian

### 3. Perhitungan Faktor Konversi:
```php
$faktorKonversi = $jumlahSatuanUtama > 0 ? ($nilaiSubSatuan / $jumlahSatuanUtama) : 0;
```

## Contoh Tampilan:

### Di Form Pembelian:
```
Konversi ke Sub Satuan (Manual)
= [32] Potong
Rumus: 8 Ekor × 4 Potong/Ekor = 32 Potong
```

### Di Detail Pembelian:
```
Konversi Manual:
= 32 Potong
Rumus: 8 Ekor × 4.0000 Potong/Ekor = 32 Potong
```

## Keuntungan Sistem:
1. **Transparansi**: User dapat melihat bagaimana perhitungan dilakukan
2. **Fleksibilitas**: Dapat menggunakan konversi manual sesuai kondisi aktual
3. **Akurasi**: Menghindari kesalahan perhitungan manual
4. **Audit Trail**: Rumus tersimpan untuk referensi di masa depan