# FIX: Nama Akun di Jurnal Umum

## Masalah
Banyak akun di jurnal umum yang namanya hanya kode akun (121, 201, dll) bukan nama lengkap seperti "Persediaan Bahan Baku", "Hutang Usaha", dll.

## Penyebab
Ada akun di tabel `accounts` yang dibuat otomatis oleh JournalService tapi namanya hanya kode akun, belum disinkronisasi dengan nama lengkap dari COA.

## Solusi

### 1. Update SyncAccountsFromCoaSeeder
Menambahkan fitur untuk:
- Mencari akun yang namanya hanya angka (kode akun)
- Update nama akun dari COA
- Jika tidak ada di COA, gunakan mapping default

### 2. Mapping Default Kode Akun
```php
'101' => 'Kas Kecil'
'102' => 'Kas di Bank'
'103' => 'Piutang Usaha'
'121' => 'Persediaan Bahan Baku'
'122' => 'Persediaan Barang Dalam Proses (WIP)'
'123' => 'Persediaan Barang Jadi'
'201' => 'Hutang Usaha'
'211' => 'Hutang Gaji (BTKL)'
'212' => 'Hutang BOP'
'401' => 'Penjualan Produk'
'501' => 'Harga Pokok Penjualan (HPP)'

// Kode 4 digit
'1101' => 'Kas Kecil'
'1102' => 'Kas di Bank'
'1103' => 'Piutang Usaha'
'1104' => 'Persediaan Bahan Baku'
'1105' => 'Persediaan Barang Dalam Proses (WIP)'
'1107' => 'Persediaan Barang Jadi'
'2101' => 'Hutang Usaha'
'2103' => 'Hutang Gaji (BTKL)'
'2104' => 'Hutang BOP'
'4101' => 'Penjualan Produk'
'5001' => 'Harga Pokok Penjualan (HPP)'
'5103' => 'Beban Penyusutan'
'5104' => 'Beban Denda dan Bunga'
'5105' => 'Penyesuaian HPP (Diskon Pembelian)'
```

## Cara Menjalankan

```bash
php artisan db:seed --class=SyncAccountsFromCoaSeeder
```

## Hasil
- 5 akun dengan nama kode diperbaiki
- Semua akun di jurnal umum sekarang punya nama lengkap
- Tampilan jurnal umum lebih profesional dan mudah dibaca

## File yang Diperbaiki
- `database/seeders/SyncAccountsFromCoaSeeder.php`

## Status
✅ SELESAI - Semua nama akun di jurnal umum sudah benar!
