# Perbaikan Kode Akun COA - Dari 3 Digit ke 4 Digit

## Masalah
Sistem menggunakan kode akun 3 digit di controller, tapi COA menggunakan 4 digit. Ini menyebabkan:
1. Jurnal pembelian kredit salah mencatat ke Piutang (aset) bukan Hutang Usaha (liabilitas)
2. Inkonsistensi antara kode akun di controller dan database COA

## Mapping Kode Akun

### AKTIVA LANCAR (1xxx)
| Kode Lama (3 digit) | Kode Baru (4 digit) | Nama Akun |
|---------------------|---------------------|-----------|
| 101 | 1101 | Kas Kecil |
| 102 | 1102 | Kas di Bank |
| 103 | 1103 | Piutang Usaha |
| 121 | 1104 | Persediaan Bahan Baku |
| 122 | 1105 | Persediaan Barang Dalam Proses (WIP) |
| 123 | 1107 | Persediaan Barang Jadi |

### AKTIVA TETAP (12xx)
| Kode Lama (3 digit) | Kode Baru (4 digit) | Nama Akun |
|---------------------|---------------------|-----------|
| 124 | 120401 | Akumulasi Penyusutan Peralatan |

### KEWAJIBAN (2xxx)
| Kode Lama (3 digit) | Kode Baru (4 digit) | Nama Akun |
|---------------------|---------------------|-----------|
| 201 | 2101 | Hutang Usaha |
| 211 | 2103 | Hutang Gaji (BTKL) |
| 212 | 2104 | Hutang BOP |

### PENDAPATAN (4xxx)
| Kode Lama (3 digit) | Kode Baru (4 digit) | Nama Akun |
|---------------------|---------------------|-----------|
| 401 | 4101 | Penjualan Produk |

### BEBAN (5xxx)
| Kode Lama (3 digit) | Kode Baru (4 digit) | Nama Akun |
|---------------------|---------------------|-----------|
| 501 | 5001 | Harga Pokok Penjualan (HPP) |
| 504 | 5103 | Beban Penyusutan |
| 505 | 5104 | Beban Denda dan Bunga |
| 506 | 5105 | Penyesuaian HPP (Diskon Pembelian) |

## Jurnal yang Diperbaiki

### 1. Pembelian Bahan Baku
**Sebelum (SALAH):**
- Pembelian Kredit: Dr 121 (Persediaan) / Cr 201 (???)

**Sesudah (BENAR):**
- Pembelian Tunai: Dr 1104 (Persediaan Bahan Baku) / Cr 1101 (Kas Kecil)
- Pembelian Transfer: Dr 1104 (Persediaan Bahan Baku) / Cr 1102 (Kas di Bank)
- Pembelian Kredit: Dr 1104 (Persediaan Bahan Baku) / Cr 2101 (Hutang Usaha) ✓

### 2. Penjualan Produk
**Sebelum:**
- Dr 103/101 / Cr 401
- Dr 501 / Cr 123

**Sesudah:**
- Dr 1103/1101 (Piutang/Kas) / Cr 4101 (Penjualan)
- Dr 5001 (HPP) / Cr 1107 (Persediaan Barang Jadi)

### 3. Produksi
**Sebelum:**
- Konsumsi Bahan: Dr 122 / Cr 121
- BTKL/BOP: Dr 122 / Cr 211, 212
- Selesai Produksi: Dr 123 / Cr 122

**Sesudah:**
- Konsumsi Bahan: Dr 1105 (WIP) / Cr 1104 (Bahan Baku)
- BTKL/BOP: Dr 1105 (WIP) / Cr 2103 (Hutang Gaji), 2104 (Hutang BOP)
- Selesai Produksi: Dr 1107 (Barang Jadi) / Cr 1105 (WIP)

### 4. Retur
**Penjualan:**
- Dr 4101 (Penjualan) / Cr 1103/1101 (Piutang/Kas)
- Dr 1107 (Barang Jadi) / Cr 5001 (HPP)

**Pembelian:**
- Dr 2101 (Hutang Usaha) / Cr 1101 (Kas) atau 2101 (Credit Note)
- Dr 2101 (Hutang Usaha) / Cr 1104 (Persediaan Bahan Baku)

### 5. Pelunasan Utang (AP Settlement)
- Dr 2101 (Hutang Usaha) / Cr 1101/1102 (Kas/Bank)
- Diskon: Cr 5105 (Penyesuaian HPP)
- Denda: Dr 5104 (Beban Denda)

### 6. Penyusutan Aset
- Dr 5103 (Beban Penyusutan) / Cr 120401 (Akumulasi Penyusutan)

## File yang Diperbaiki
1. ✓ app/Http/Controllers/PembelianController.php
2. ✓ app/Http/Controllers/PenjualanController.php
3. ✓ app/Http/Controllers/ProduksiController.php
4. ✓ app/Http/Controllers/ReturController.php
5. ✓ app/Http/Controllers/ApSettlementController.php
6. ✓ app/Http/Controllers/AsetDepreciationController.php
7. ✓ database/seeders/CompleteCoaSeeder.php
8. ✓ app/Exports/JurnalUmumExport.php (diganti dengan PhpSpreadsheet murni)
9. ✓ app/Http/Controllers/AkuntansiController.php

## Cara Menjalankan
```bash
php artisan db:seed --class=CompleteCoaSeeder
```

## Status
✅ SELESAI - Semua kode akun sudah diperbaiki dari 3 digit ke 4 digit
✅ Export Excel jurnal umum sudah diperbaiki tanpa perlu install package tambahan
