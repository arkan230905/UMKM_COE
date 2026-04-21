# FIX: Penggajian Dedi Gunawan Missing from Buku Besar (General Ledger)

## MASALAH
User melaporkan bahwa **Penggajian Dedi Gunawan (26/04/2026, Rp 3.250.000) tidak muncul di Buku Besar Kas (COA 112)**, padahal data tersebut ada di Jurnal Umum.

## ROOT CAUSE
Ditemukan bug di code yang **secara sengaja mengecualikan (exclude) semua penggajian entries** dari tampilan Buku Besar:

### File 1: `app/Http/Controllers/AkuntansiController.php` (Line 540)
```php
// SEBELUM (SALAH):
->whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'sales_return', 'debt_payment', 'penggajian'])

// SESUDAH (BENAR):
->whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'sales_return', 'debt_payment'])
```

### File 2: `app/Exports/BukuBesarExport.php` (Line 200)
```php
// SEBELUM (SALAH):
->whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'sales_return', 'debt_payment', 'penggajian'])

// SESUDAH (BENAR):
->whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'sales_return', 'debt_payment'])
```

## PENJELASAN
Sistem SIMCOST menggunakan **2 tabel journal yang berbeda**:
1. **`journal_entries` + `journal_lines`** - Sistem baru untuk automated entries (purchase, sale, production, dll)
2. **`jurnal_umum`** - Sistem lama untuk manual/legacy entries

Ketika menampilkan Buku Besar, code mengambil data dari kedua tabel:
- Dari `journal_entries` untuk transaksi automated
- Dari `jurnal_umum` untuk transaksi manual yang belum dimigrasikan

**Masalahnya**: Code mengecualikan 'penggajian' dari `jurnal_umum` karena asumsi bahwa semua penggajian sudah ada di `journal_entries`. Tapi Penggajian Dedi Gunawan hanya ada di `jurnal_umum`, bukan di `journal_entries`.

## SOLUSI
Hapus 'penggajian' dari exclusion list di kedua file. Ini memungkinkan penggajian entries dari `jurnal_umum` untuk ditampilkan di Buku Besar.

## FILE YANG DIPERBAIKI
1. ✅ `app/Http/Controllers/AkuntansiController.php` - Method `bukuBesar()` (Line 578)
2. ✅ `app/Exports/BukuBesarExport.php` - Method `getAccountTransactions()` (Line 200)

## VERIFIKASI
Jalankan script: `http://127.0.0.1:8000/verify-dedi-fix.php`

Atau cek manual:
1. Buka Buku Besar Kas (COA 112)
2. Cari tanggal 26/04/2026
3. Penggajian Dedi Gunawan harus muncul dengan:
   - Debit: Rp 3.250.000 (ke BTKTL - COA 54)
   - Kredit: Rp 3.250.000 (dari Kas - COA 112)

## DAMPAK
- ✅ Penggajian Dedi Gunawan akan muncul di Buku Besar Kas
- ✅ Total Debit dan Kredit di Buku Besar akan benar
- ✅ Neraca Saldo akan sesuai dengan Jurnal Umum
- ✅ Laporan keuangan akan akurat

## CATATAN PENTING
Ini adalah **bug di logic**, bukan masalah data. Data Penggajian Dedi sudah benar di database, hanya tidak ditampilkan karena filter yang salah.

Jika ada penggajian entries lain yang juga tidak muncul, mereka akan muncul setelah fix ini diterapkan.
