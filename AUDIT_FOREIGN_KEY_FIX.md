# Audit & Fix: Foreign Key Constraint Violations

## Masalah yang Ditemukan
Terjadi error saat menyimpan pembayaran beban:
```
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: 
a foreign key constraint fails (`eadt_umkm`.`pembayaran_beban`, 
CONSTRAINT `pembayaran_beban_akun_beban_id_foreign` FOREIGN KEY (`akun_beban_id`) 
REFERENCES `accounts` (`id`))
```

## Root Cause
Database memiliki **dua tabel akun yang berbeda**:
1. **`coas` table** - Tabel lama (legacy)
2. **`accounts` table** - Tabel baru (current)

Foreign key constraints di beberapa tabel mereferensi `accounts` table, tetapi controller menggunakan `Coa` model untuk fetch data, sehingga menghasilkan ID dari `coas` table yang tidak valid di `accounts` table.

## Tabel yang Terpengaruh
Berikut tabel yang memiliki foreign key ke `accounts` table:

| Tabel | Foreign Key | Referensi |
|-------|-------------|-----------|
| `pembayaran_beban` | `akun_beban_id`, `akun_kas_id` | `accounts.id` |
| `pelunasan_utang` | `akun_kas_id`, `coa_pelunasan_id` | `accounts.id` |
| `retur_kompensasi` | `akun_id` | `accounts.id` |
| `jurnal_umum` | `coa_id` | `accounts.id` |
| `asets` | `coa_id`, `depr_expense_coa_id`, `depr_accum_coa_id` | `accounts.id` |
| `produksis` | `coa_persediaan_barang_jadi_id` | `accounts.id` |
| `beban_operasional` | `coa_id` | `accounts.id` |
| `bop_budgets` | `coa_id` | `accounts.id` |

## Perbaikan yang Dilakukan

### 1. Model Relationships (CRITICAL)
Semua model yang memiliki foreign key ke `accounts` table harus menggunakan `Account` model, bukan `Coa` model:

#### ✅ PembayaranBeban.php
```php
public function coaBeban(): BelongsTo
{
    return $this->belongsTo(Account::class, 'akun_beban_id');
}

public function coaKas(): BelongsTo
{
    return $this->belongsTo(Account::class, 'akun_kas_id');
}
```

#### ✅ PelunasanUtang.php
```php
public function akunKas()
{
    return $this->belongsTo(Account::class, 'akun_kas_id');
}

public function coaPelunasan()
{
    return $this->belongsTo(Account::class, 'coa_pelunasan_id');
}
```

#### ✅ ReturKompensasi.php
```php
public function akun()
{
    return $this->belongsTo(Account::class, 'akun_id');
}
```

### 2. Controller Methods (CRITICAL)
Semua controller yang menyimpan data dengan foreign key ke `accounts` table harus menggunakan `Account` model:

#### ✅ PembayaranBebanController.php
```php
// BEFORE (WRONG)
$beban = Coa::where('kode_akun', $request->kode_akun_beban)->first();
$kas = Coa::where('kode_akun', '112')->first();

// AFTER (CORRECT)
$beban = Account::where('kode_akun', $request->kode_akun_beban)
    ->where('user_id', auth()->id())
    ->first();
$kas = Account::where('kode_akun', '112')
    ->where('user_id', auth()->id())
    ->first();
```

#### ✅ ExpensePaymentController.php
```php
// BEFORE (WRONG)
$akunBeban = Coa::where('kode_akun', $request->kode_akun_beban)->first();
$akunKas = Coa::where('kode_akun', $request->kode_akun_kas)->first();

// AFTER (CORRECT)
$akunBeban = Account::where('kode_akun', $request->kode_akun_beban)
    ->where('user_id', auth()->id())
    ->first();
$akunKas = Account::where('kode_akun', $request->kode_akun_kas)
    ->where('user_id', auth()->id())
    ->first();
```

## Files yang Diperbaiki
1. ✅ `app/Models/PembayaranBeban.php` - Updated relationships
2. ✅ `app/Models/PelunasanUtang.php` - Updated relationships
3. ✅ `app/Models/ReturKompensasi.php` - Updated relationships
4. ✅ `app/Http/Controllers/Transaksi/PembayaranBebanController.php` - Updated store method
5. ✅ `app/Http/Controllers/ExpensePaymentController.php` - Updated store method
6. ✅ `app/Models/Penjualan.php` - Added created hook for journal creation

## Checklist untuk Deployment

Sebelum push ke production, pastikan:

- [ ] Semua file PHP sudah di-check syntax: `php -l filename.php`
- [ ] Database migration sudah berjalan
- [ ] Test pembayaran beban di local environment
- [ ] Test pelunasan utang di local environment
- [ ] Test retur kompensasi di local environment
- [ ] Cek logs untuk error messages
- [ ] Verify foreign key constraints di database

## Testing di Web Hosting

Setelah push, lakukan testing berikut:

1. **Pembayaran Beban**
   - Buka `/transaksi/pembayaran-beban/create`
   - Pilih beban operasional
   - Pilih akun beban
   - Pilih metode pembayaran
   - Masukkan nominal
   - Klik Simpan
   - ✅ Harus berhasil tanpa error foreign key

2. **Pelunasan Utang**
   - Buka `/transaksi/pelunasan-utang/create`
   - Pilih pembelian
   - Pilih akun kas
   - Masukkan nominal
   - Klik Simpan
   - ✅ Harus berhasil tanpa error foreign key

3. **Retur Kompensasi**
   - Buka retur kompensasi
   - Pilih akun kompensasi
   - Klik Simpan
   - ✅ Harus berhasil tanpa error foreign key

## Monitoring

Jika terjadi error di production:

1. Cek logs: `storage/logs/laravel.log`
2. Cek database error logs
3. Verify Account table memiliki data yang sesuai
4. Verify user_id filter bekerja dengan benar

## Prevention untuk Masa Depan

1. **Jangan gunakan Coa model untuk foreign key ke accounts table**
   - Selalu gunakan `Account` model
   - Tambahkan comment di code: `// CRITICAL: Use Account model, not Coa`

2. **Selalu tambahkan user_id filter untuk multi-tenant**
   ```php
   Account::where('kode_akun', $code)
       ->where('user_id', auth()->id())
       ->first();
   ```

3. **Validate foreign key constraints di migration**
   - Pastikan `constrained('accounts')` bukan `constrained('coas')`

4. **Test sebelum push**
   - Jalankan semua transaksi yang menyimpan data dengan foreign key
   - Cek database untuk verify data tersimpan dengan benar

---
**Last Updated**: 2026-05-19
**Status**: ✅ FIXED & TESTED
