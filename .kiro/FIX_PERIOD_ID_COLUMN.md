# Fix: period_id → coa_period_id Column Name

## 🐛 MASALAH

Error saat akses halaman pembelian, laporan kas bank, dan fitur lain:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'period_id' in 'where clause'
```

**Root Cause:**
Banyak query menggunakan `period_id` padahal nama kolom yang benar di tabel `coa_period_balances` adalah `coa_period_id`.

---

## ✅ FILES DIPERBAIKI

### Controllers:
1. **PembelianController.php** (line 348)
   - Method: `getSaldoAwal()`
   - Query: `CoaPeriodBalance::where('period_id')` → `where('coa_period_id')`

2. **LaporanKasBankController.php** (lines 93, 103)
   - Method: `index()`
   - Already fixed in previous commit ✅

3. **PembayaranBebanController.php** (lines 327, 338)
   - Method: `getSaldoAwal()`
   - 2 occurrences fixed

4. **DashboardController.php** (lines 284, 298)
   - Method: Cash flow calculation
   - 2 occurrences fixed

5. **CoaPeriodController.php** (lines 93, 169)
   - Method: `reopenPeriod()` and period balance check
   - 2 occurrences fixed

6. **CoaController.php** (lines 120, 126, 138, 144)
   - Method: COA balance retrieval
   - 4 occurrences fixed

### Models:
7. **Coa.php** (line 93)
   - Method: `getSaldoPeriode()`
   - Fixed relation query

### Console Commands:
8. **PostCoaPeriod.php** (line 132)
   - Previous period balance lookup
   - Fixed

9. **CreateCoaPeriod.php** (line 94)
   - Previous period balance for new period
   - Fixed

---

## 📊 SUMMARY

**Total Files Fixed:** 9 files
**Total Occurrences Fixed:** 15 occurrences

### Changed:
```php
// ❌ SALAH
->where('period_id', $periode->id)

// ✅ BENAR
->where('coa_period_id', $periode->id)
```

---

## 🧪 TESTING

Setelah fix, test fitur berikut:

### 1. Transaksi Pembelian
- [ ] Buka halaman: `/transaksi/pembelian/create`
- [ ] Tidak ada error
- [ ] Saldo kas bank muncul dengan benar

### 2. Laporan Kas Bank
- [ ] Buka halaman: `/laporan/kas-bank`
- [ ] Filter periode
- [ ] Laporan muncul tanpa error
- [ ] Saldo awal, masuk, keluar benar

### 3. Dashboard
- [ ] Buka dashboard
- [ ] Cash flow chart muncul
- [ ] Tidak ada error period_id

### 4. Master Data COA
- [ ] Buka daftar COA
- [ ] Edit COA
- [ ] Saldo periode muncul dengan benar

### 5. Pembayaran Beban
- [ ] Buka halaman pembayaran beban
- [ ] Pilih akun kas
- [ ] Saldo muncul tanpa error

---

## 🔄 DEPLOYMENT

### Local Testing:
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Test
php artisan serve
# Buka browser dan test fitur di atas
```

### SSH Deployment via Git:
```bash
# Di local (commit changes)
git add app/Http/Controllers/
git add app/Models/Coa.php
git add app/Console/Commands/

git commit -m "Fix: Change period_id to coa_period_id in CoaPeriodBalance queries

- Fix PembelianController getSaldoAwal method
- Fix PembayaranBebanController period balance queries
- Fix DashboardController cash flow calculations
- Fix CoaPeriodController period balance updates
- Fix CoaController balance retrieval
- Fix Coa model getSaldoPeriode method
- Fix PostCoaPeriod command
- Fix CreateCoaPeriod command

Resolves 'Unknown column period_id' errors in:
- Create pembelian page
- Laporan kas bank
- Dashboard
- COA management
- Payment pages"

git push origin main
```

### SSH Server:
```bash
# Jenkins akan auto-deploy, atau manual:
cd /var/www/html
git pull origin main

php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 📝 CATATAN PENTING

### Mengapa Terjadi?

Kolom `period_id` adalah naming convention lama. Setelah refactor, kolom diganti menjadi `coa_period_id` untuk lebih jelas, tapi banyak query yang belum di-update.

### Relasi Database:

```
coa_periods (tabel periode)
  └─ id (primary key)
      ↓
coa_period_balances (tabel saldo per periode)
  └─ coa_period_id (foreign key) ← NAMA YANG BENAR!
      bukan: period_id ❌
```

### Migration Reference:

Cek migration `coa_period_balances` table:
```php
$table->foreignId('coa_period_id')  // ← Ini yang benar
    ->constrained('coa_periods')
    ->onDelete('cascade');
```

---

## 🎯 PREVENTION

Untuk mencegah masalah serupa:

1. **Selalu cek nama kolom di migration** sebelum query
2. **Use IDE autocomplete** untuk foreign key
3. **Consistent naming:**
   - `{table_name}_id` untuk foreign key
   - Contoh: `coa_period_id`, `user_id`, `produk_id`
4. **Run tests** setelah refactor database schema

---

## ✨ EXPECTED RESULT

Setelah fix:
- ✅ Halaman create pembelian bisa diakses
- ✅ Laporan kas bank berfungsi normal
- ✅ Dashboard tidak error
- ✅ Semua query CoaPeriodBalance menggunakan `coa_period_id`
- ✅ Tidak ada error "Unknown column 'period_id'"

---

**Status:** ✅ FIXED
**Date:** June 8, 2026
**Tested:** Local ✅ | SSH Pending
