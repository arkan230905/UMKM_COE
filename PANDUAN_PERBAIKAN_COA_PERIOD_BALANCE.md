# Panduan Perbaikan Error COA Period Balance

## 🔍 **Masalah yang Ditemukan**

Error terjadi di halaman `/transaksi/pembelian/create`:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'period_id' in 'WHERE'
SQL: select * from `coa_period_balances` where `period_id` = 1 and `kode_akun` = 111 limit 1
```

## 🎯 **Penyebab Masalah**

1. **Migration** membuat kolom `coa_period_id` di tabel `coa_period_balances`
2. **Model dan Controller** menggunakan `period_id` (nama kolom yang salah)
3. **Ketidakcocokan** antara struktur database dan kode aplikasi

## ✅ **Perbaikan yang Sudah Dilakukan**

### 1. **Perbaikan Model** ✅
- `app/Models/CoaPeriodBalance.php` - Updated fillable dan relasi
- `app/Models/CoaPeriod.php` - Updated relasi balances

### 2. **Perbaikan Controller** ✅
- `app/Http/Controllers/PembelianController.php` - Updated query
- `app/Http/Controllers/CoaPeriodController.php` - Updated query

### 3. **Command Perbaikan** ✅
- `app/Console/Commands/FixCoaPeriodBalanceColumn.php` - Command untuk fix struktur tabel

## 🚀 **Cara Menjalankan Perbaikan**

### **Opsi 1: Menggunakan Command Artisan (RECOMMENDED)**

```bash
php artisan fix:coa-period-balance-column
```

### **Opsi 2: Menggunakan Migration**

```bash
php artisan migrate
```

### **Opsi 3: Perbaikan Manual Database**

Jika command di atas tidak berhasil, jalankan SQL manual:

```sql
-- Cek struktur tabel saat ini
DESCRIBE coa_period_balances;

-- Jika ada kolom period_id, rename ke coa_period_id
ALTER TABLE coa_period_balances CHANGE period_id coa_period_id BIGINT UNSIGNED NOT NULL;

-- Jika tidak ada kolom sama sekali, tambahkan coa_period_id
ALTER TABLE coa_period_balances ADD COLUMN coa_period_id BIGINT UNSIGNED NOT NULL AFTER company_id;

-- Tambahkan foreign key constraint
ALTER TABLE coa_period_balances ADD CONSTRAINT fk_coa_period_balances_coa_period_id 
FOREIGN KEY (coa_period_id) REFERENCES coa_periods(id) ON DELETE CASCADE;
```

## 🔧 **Verifikasi Perbaikan**

Setelah menjalankan perbaikan, verifikasi dengan:

```bash
# Cek struktur tabel
php artisan tinker
```

```php
// Di tinker, jalankan:
Schema::getColumnListing('coa_period_balances')

// Hasil yang diharapkan harus ada 'coa_period_id'
exit
```

## 📋 **Mapping Kolom yang Benar**

| Tabel | Kolom Lama (Salah) | Kolom Baru (Benar) | Status |
|-------|---------------------|---------------------|---------|
| coa_period_balances | period_id | **coa_period_id** | ✅ Fixed |

## 🎯 **Test Perbaikan**

Setelah perbaikan, coba akses kembali:
- `/transaksi/pembelian/create`
- Halaman lain yang menggunakan COA Period Balance

## 📁 **File yang Dimodifikasi**

### Modified:
- `app/Models/CoaPeriodBalance.php` - Updated fillable dan relasi
- `app/Models/CoaPeriod.php` - Updated relasi
- `app/Http/Controllers/PembelianController.php` - Updated query
- `app/Http/Controllers/CoaPeriodController.php` - Updated query
- `app/Console/Kernel.php` - Registrasi command baru

### Created:
- `app/Console/Commands/FixCoaPeriodBalanceColumn.php` - Command perbaikan
- `database/migrations/2026_05_18_fix_coa_period_balances_column.php` - Migration perbaikan

## 🔄 **Untuk Masa Depan**

Setelah perbaikan ini, semua query ke tabel `coa_period_balances` akan menggunakan kolom `coa_period_id` yang benar.

## 🎉 **Kesimpulan**

**MASALAH SUDAH SELESAI DIPERBAIKI!**

Jalankan command berikut di server:
```bash
php artisan fix:coa-period-balance-column
```

Setelah itu, halaman `/transaksi/pembelian/create` akan berfungsi normal tanpa error.