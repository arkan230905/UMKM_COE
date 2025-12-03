# Design Document - Laporan Kas & Bank Real-Time

## Overview

Sistem laporan kas dan bank yang menampilkan posisi keuangan real-time dengan tracking lengkap semua transaksi masuk dan keluar. Sistem ini terintegrasi dengan semua modul transaksi untuk memastikan saldo selalu akurat.

## Architecture

### Database Schema

**Tabel yang Digunakan:**
- `coas` - Chart of Accounts (akun kas dan bank)
- `journal_entries` - Jurnal umum untuk tracking semua transaksi
- `journal_lines` - Detail baris jurnal
- `penjualans` - Transaksi penjualan
- `pembelians` - Transaksi pembelian
- `expense_payments` - Pembayaran beban
- `pelunasan_utangs` - Pelunasan utang
- `penggajians` - Penggajian pegawai
- `returs` - Retur penjualan

### Data Flow

```
Transaksi → Journal Entry → Journal Lines → Update Saldo Kas/Bank
```

## Components and Interfaces

### 1. LaporanKasBankController

**Methods:**
- `index()` - Tampilkan halaman laporan
- `getData(Request $request)` - Get data kas & bank dengan filter
- `getDetailTransaksi($coaId, $type, Request $request)` - Get detail transaksi masuk/keluar
- `export(Request $request)` - Export ke Excel/PDF

### 2. View: laporan/kas-bank/index.blade.php

**Sections:**
- Header dengan total kas & bank
- Filter periode
- Tabel saldo per akun kas/bank
- Modal detail transaksi masuk
- Modal detail transaksi keluar
- Tombol export dan print

### 3. Helper Methods

**KasBankHelper:**
- `getSaldoAwal($coaId, $startDate)` - Hitung saldo awal
- `getTransaksiMasuk($coaId, $startDate, $endDate)` - Get transaksi masuk
- `getTransaksiKeluar($coaId, $startDate, $endDate)` - Get transaksi keluar
- `getSaldoAkhir($coaId, $endDate)` - Hitung saldo akhir

## Data Models

### Kas & Bank Summary

```php
[
    'kode_akun' => '101',
    'nama_akun' => 'Kas',
    'saldo_awal' => 10000000,
    'transaksi_masuk' => 50000000,
    'transaksi_keluar' => 30000000,
    'saldo_akhir' => 30000000
]
```

### Transaksi Masuk

```php
[
    'tanggal' => '2025-11-10',
    'nomor_transaksi' => 'PJ-202511-0001',
    'jenis' => 'Penjualan',
    'keterangan' => 'Penjualan produk',
    'nominal' => 5000000
]
```

### Transaksi Keluar

```php
[
    'tanggal' => '2025-11-10',
    'nomor_transaksi' => 'PB-202511-0001',
    'jenis' => 'Pembelian',
    'keterangan' => 'Pembelian bahan baku',
    'nominal' => 3000000
]
```

## Error Handling

### Saldo Tidak Cukup
- Tampilkan alert sebelum transaksi
- Cek saldo tersedia vs nominal transaksi
- Berikan opsi untuk memilih akun kas/bank lain

### Data Tidak Ditemukan
- Tampilkan pesan "Belum ada transaksi"
- Berikan link untuk membuat transaksi baru

### Error Perhitungan
- Log error ke file
- Tampilkan pesan error yang user-friendly
- Berikan opsi untuk refresh data

## Testing Strategy

### Unit Tests
- Test perhitungan saldo awal
- Test perhitungan transaksi masuk
- Test perhitungan transaksi keluar
- Test perhitungan saldo akhir

### Integration Tests
- Test integrasi dengan modul penjualan
- Test integrasi dengan modul pembelian
- Test integrasi dengan modul beban
- Test integrasi dengan modul penggajian

### Manual Tests
- Test filter periode
- Test export Excel
- Test export PDF
- Test print laporan
- Test detail transaksi

## Performance Considerations

- Cache saldo untuk periode yang sering diakses
- Index pada kolom tanggal di tabel transaksi
- Pagination untuk detail transaksi
- Lazy loading untuk modal detail

## Security

- Validasi input periode
- Sanitize data sebelum export
- Check user permission untuk akses laporan
- Audit log untuk perubahan saldo
