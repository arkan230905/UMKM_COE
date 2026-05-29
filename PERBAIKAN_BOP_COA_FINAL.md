# Perbaikan BOP COA - Final Fix

## Masalah
Komponen BOP menampilkan "210 - Hutang Usaha (BOP Lain-lain)" untuk banyak item (BTKTL, Minyak Goreng, Tepung Terigu, Tepung Maizena, Lada, Bubuk Bawang Putih, Bubuk Kaldu Ayam) padahal seharusnya menggunakan akun COA BOP yang spesifik.

## Akar Masalah
Database memiliki `coa_id` di field `komponen_bop` JSON, tetapi nilai `coa_id` tersebut menunjuk ke COA yang SALAH:
- Tepung Terigu, Tepung Maizena, Lada, Bubuk Bawang Putih, Bubuk Kaldu → Semua menunjuk ke COA ID 10 "115 - Pers. Bahan Pendukung" (seharusnya akun BOP individual)
- Minyak Goreng → Menunjuk ke COA ID 10 "115 - Pers. Bahan Pendukung" (seharusnya "532 - BOP-Minyak Goreng")
- Kemasan → Menunjuk ke COA ID 56 "532 - BOP-Minyak Goreng" (salah, seharusnya "538 - BOP-Kemasan")
- Penyusutan Mesin/Alat → Menunjuk ke COA ID 57 "533 - BOP-Tepung Terigu" (salah)
- Gas/BBM → Menunjuk ke COA ID 74 "552 - BOP TL - Biaya Penyusutan Gedung" (salah)
- BTKTL → Menunjuk ke COA ID 35 "211 - Hutang Gaji" (seharusnya akun BOP BTKTL)

## Solusi yang Diterapkan

### 1. Identifikasi COA yang Benar
Berdasarkan struktur COA di database, mapping yang benar adalah:

| Komponen BOP | COA ID | Kode Akun | Nama Akun |
|--------------|--------|-----------|-----------|
| Minyak Goreng | 56 | 532 | BOP-Minyak Goreng |
| Tepung Terigu | 57 | 533 | BOP-Tepung Terigu |
| Tepung Maizena | 58 | 534 | BOP-Tepung Maizena |
| Lada | 59 | 535 | BOP- Lada |
| Bubuk Kaldu Ayam | 60 | 536 | BOP- Bubuk Kaldu |
| Bubuk Bawang Putih | 61 | 537 | BOP- Bubuk Bawang Putih |
| Kemasan | 62 | 538 | BOP-Kemasan |
| BTKTL | 70 | 546 | BOP BTKTL - BTKTL Lainnya |
| Listrik Mesin/Mixer | 72 | 550 | BOP TL - Biaya Listrik |
| Air & Kebersihan | 78 | 556 | BOP TL - Biaya Air |
| Kebersihan | 78 | 556 | BOP TL - Biaya Air |
| Gas / BBM | 79 | 557 | BOP TL - Lainnya |
| Penyusutan Mesin | 77 | 555 | BOP TL - Biaya Penyusutan Mesin |
| Penyusutan Alat | 75 | 553 | BOP TL - Biaya Penyusutan Peralatan |
| Maintenance | 79 | 557 | BOP TL - Lainnya |

### 2. Update Database
Script `fix_bop_coa_ids.php` telah dijalankan untuk memperbarui semua `coa_id` di field `komponen_bop` JSON dengan nilai yang benar.

**Hasil Update:**

#### BOP: Penggorengan (ID: 1)
- ✓ Listrik Mesin: 550 - BOP TL - Biaya Listrik (sudah benar)
- ✓ Gas / BBM: 557 - BOP TL - Lainnya (diperbaiki dari 552)
- ✓ Maintenance: 557 - BOP TL - Lainnya (diperbaiki dari 210)
- ✓ Penyusutan Mesin: 555 - BOP TL - Biaya Penyusutan Mesin (diperbaiki dari 533)
- ✓ Air & Kebersihan: 556 - BOP TL - Biaya Air (diperbaiki dari 551)
- ✓ BTKTL: 546 - BOP BTKTL - BTKTL Lainnya (diperbaiki dari 211)
- ✓ Minyak Goreng: 532 - BOP-Minyak Goreng (diperbaiki dari 115)

#### BOP: Perbumbuan Ayam Goreng (ID: 2)
- ✓ Listrik Mixer: 550 - BOP TL - Biaya Listrik (sudah benar)
- ✓ Penyusutan Alat: 553 - BOP TL - Biaya Penyusutan Peralatan (diperbaiki dari 533)
- ✓ Maintenance: 557 - BOP TL - Lainnya (diperbaiki dari 210)
- ✓ Kebersihan: 556 - BOP TL - Biaya Air (diperbaiki dari 536)
- ✓ BTKTL: 546 - BOP BTKTL - BTKTL Lainnya (diperbaiki dari 211)
- ✓ Tepung Terigu: 533 - BOP-Tepung Terigu (diperbaiki dari 115)
- ✓ Tepung Maizena: 534 - BOP-Tepung Maizena (diperbaiki dari 115)
- ✓ Lada: 535 - BOP- Lada (diperbaiki dari 115)
- ✓ Bubuk Bawang Putih: 537 - BOP- Bubuk Bawang Putih (diperbaiki dari 115)
- ✓ Bubuk Kaldu Ayam: 536 - BOP- Bubuk Kaldu (diperbaiki dari 115)

#### BOP: Pengemasan (ID: 3)
- ✓ Listrik: 550 - BOP TL - Biaya Listrik (sudah benar)
- ✓ Penyusutan Alat: 553 - BOP TL - Biaya Penyusutan Peralatan (diperbaiki dari 533)
- ✓ Kemasan: 538 - BOP-Kemasan (diperbaiki dari 532)
- ✓ Kebersihan: 556 - BOP TL - Biaya Air (diperbaiki dari 536)
- ✓ BTKTL: 546 - BOP BTKTL - BTKTL Lainnya (diperbaiki dari 211)

### 3. Clear Cache
Semua cache Laravel telah dibersihkan:
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

## Cara Verifikasi

### 1. Hard Refresh Browser
Tekan **Ctrl + Shift + R** di browser untuk memuat ulang halaman tanpa cache.

### 2. Cek Halaman Create Produksi
Buka halaman "Tambah Data Produksi" dan pilih produk "Ayam Goreng Bundo". Sekarang seharusnya menampilkan:

**Biaya Overhead Pabrik (BOP):**
- Listrik Mesin: COA 550 - BOP TL - Biaya Listrik ✓
- Gas / BBM: COA 557 - BOP TL - Lainnya ✓
- Maintenance: COA 557 - BOP TL - Lainnya ✓
- Penyusutan Mesin: COA 555 - BOP TL - Biaya Penyusutan Mesin ✓
- Air & Kebersihan: COA 556 - BOP TL - Biaya Air ✓
- BTKTL: COA 546 - BOP BTKTL - BTKTL Lainnya ✓
- Minyak Goreng: COA 532 - BOP-Minyak Goreng ✓
- Listrik Mixer: COA 550 - BOP TL - Biaya Listrik ✓
- Penyusutan Alat: COA 553 - BOP TL - Biaya Penyusutan Peralatan ✓
- Maintenance: COA 557 - BOP TL - Lainnya ✓
- Kebersihan: COA 556 - BOP TL - Biaya Air ✓
- BTKTL: COA 546 - BOP BTKTL - BTKTL Lainnya ✓
- Tepung Terigu: COA 533 - BOP-Tepung Terigu ✓
- Tepung Maizena: COA 534 - BOP-Tepung Maizena ✓
- Lada: COA 535 - BOP- Lada ✓
- Bubuk Bawang Putih: COA 537 - BOP- Bubuk Bawang Putih ✓
- Bubuk Kaldu Ayam: COA 536 - BOP- Bubuk Kaldu ✓
- Listrik: COA 550 - BOP TL - Biaya Listrik ✓
- Penyusutan Alat: COA 553 - BOP TL - Biaya Penyusutan Peralatan ✓
- Kemasan: COA 538 - BOP-Kemasan ✓
- Kebersihan: COA 556 - BOP TL - Biaya Air ✓
- BTKTL: COA 546 - BOP BTKTL - BTKTL Lainnya ✓

**TIDAK ADA LAGI "210 - Hutang Usaha (BOP Lain-lain)"** kecuali untuk komponen yang memang tidak memiliki akun BOP spesifik.

### 3. Cek Jurnal Produksi
Jurnal produksi sekarang akan menggunakan akun COA BOP yang benar, bukan lagi "210 - Hutang Usaha".

## File yang Terlibat

### Script Helper
- `fix_bop_coa_ids.php` - Script untuk memperbaiki COA ID di database
- `check_bop_coa_current.php` - Script untuk verifikasi COA ID
- `list_bop_coa.php` - Script untuk melihat daftar COA BOP
- `find_coa_table.php` - Script untuk menemukan nama tabel COA

### Controller (Sudah Benar)
- `app/Http/Controllers/ProduksiController.php` - Sudah menggunakan `coa_id` dari database
- `app/Http/Controllers/BomController.php` - Sudah menggunakan `coa_id` dari database

### Database
- Tabel: `bop_proses`
- Field: `komponen_bop` (JSON) - Setiap komponen sekarang memiliki `coa_id` yang benar

## Catatan Penting

1. **Controller sudah benar** - Kode controller sudah memprioritaskan `coa_id` dari database sejak perbaikan sebelumnya
2. **Database yang salah** - Masalahnya adalah nilai `coa_id` di database yang menunjuk ke COA yang salah
3. **Sekarang sudah diperbaiki** - Semua `coa_id` di database sudah diperbarui ke nilai yang benar
4. **Cache sudah dibersihkan** - Semua cache Laravel sudah dibersihkan
5. **Hard refresh diperlukan** - User harus hard refresh browser (Ctrl+Shift+R) untuk melihat perubahan

## Status
✅ **SELESAI** - Semua BOP COA sudah menggunakan akun yang benar, tidak ada lagi "210 - Hutang Usaha (BOP Lain-lain)" untuk komponen yang seharusnya memiliki akun BOP spesifik.
