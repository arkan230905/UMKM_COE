# Implementasi Nomor Transaksi Otomatis

## Overview
Sistem nomor transaksi otomatis untuk Pembelian dan Penjualan dengan format:
- **Pembelian**: `PB-YYYYMMDD-0001`
- **Penjualan**: `PJ-YYYYMMDD-0001`

## 1. Migration Penjualan

```bash
php artisan make:migration add_nomor_penjualan_to_penjualans_table
php artisan migrate
```

## 2. Update Model Penjualan

Tambahkan auto-generate nomor di model `Penjualan.php`:
- Tambah `nomor_penjualan` ke `$fillable`
- Tambah event `creating` untuk auto-generate nomor

## 3. Update View - Daftar Pembelian

File: `resources/views/transaksi/pembelian/index.blade.php`
- Tambah kolom "No. Transaksi" di tabel
- Tampilkan `$pembelian->nomor_pembelian`

## 4. Update View - Daftar Penjualan

File: `resources/views/transaksi/penjualan/index.blade.php`
- Tambah kolom "No. Transaksi" di tabel
- Tampilkan `$penjualan->nomor_penjualan`

## 5. Update Laporan Pembelian

File: `resources/views/laporan/pembelian.blade.php`
- Tambah kolom "No. Transaksi" di laporan
- Export Excel juga include nomor transaksi

## 6. Update Laporan Penjualan

File: `resources/views/laporan/penjualan.blade.php`
- Tambah kolom "No. Transaksi" di laporan
- Export Excel juga include nomor transaksi

## Status Implementasi

- [x] Migration pembelian
- [x] Model pembelian auto-generate
- [ ] Migration penjualan
- [ ] Model penjualan auto-generate
- [ ] View daftar pembelian
- [ ] View daftar penjualan
- [ ] Laporan pembelian
- [ ] Laporan penjualan
- [ ] Export Excel pembelian
- [ ] Export Excel penjualan
