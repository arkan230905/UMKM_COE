# Deployment Instructions

## Perbaikan Foreign Key dan Saldo Kas Bank

### Update: 01 Juli 2026

### 🔧 Perubahan yang Dilakukan:

#### 1. **Fix Foreign Key Constraint** (CRITICAL - HARUS DIJALANKAN DI SERVER)
   - File: `database/migrations/2026_07_01_222727_fix_penjualans_coa_id_foreign_key.php`
   - Masalah: Foreign key `coa_id` di tabel `penjualans` dan `pembelians` salah mereferensi tabel `accounts`, seharusnya ke `coas`
   - Fix: Migration akan drop foreign key lama dan buat yang baru ke tabel `coas`

#### 2. **Fix Saldo Laporan Kas Bank**
   - File: `app/Http/Controllers/LaporanKasBankController.php`
   - Perubahan: Saldo awal prioritas dari `saldo_awal` di Master Data COA
   - Uang Masuk/Keluar dari `jurnal_umum` (debit/kredit)

#### 3. **Fix Saldo di Form Pembelian**
   - File: `app/Http/Controllers/PembelianController.php`
   - Perubahan: Helper methods `getSaldoAwalHelper`, `getTransaksiMasukHelper`, `getTransaksiKeluarHelper` menggunakan logika yang sama dengan Laporan Kas Bank

---

## 📋 Langkah Deploy di Production Server:

### STEP 1: Pull Code Terbaru
```bash
cd /var/www/html
git pull origin main
```

### STEP 2: Jalankan Migration (WAJIB!)
```bash
php artisan migrate --path=database/migrations/2026_07_01_222727_fix_penjualans_coa_id_foreign_key.php
```

**Output yang diharapkan:**
```
INFO  Running migrations.
2026_07_01_222727_fix_penjualans_coa_id_foreign_key .... DONE
```

### STEP 3: Clear Cache
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### STEP 4: Restart Services (Optional)
```bash
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

### STEP 5: Verifikasi
1. **Test Penjualan**:
   - Buat penjualan baru dengan pembayaran cash/transfer
   - Pastikan tidak ada error "foreign key constraint"

2. **Test Pembelian**:
   - Buka form pembelian
   - Cek apakah saldo di dropdown metode pembayaran sesuai dengan Laporan Kas Bank

3. **Test Laporan Kas Bank**:
   - Buka Laporan > Kas & Bank
   - Pastikan Saldo Awal terisi dari saldo_awal di Master Data COA
   - Pastikan Uang Masuk/Keluar sesuai dengan transaksi pembelian/penjualan

---

## ⚠️ IMPORTANT NOTES:

### Untuk User:
1. **Isi Saldo Awal di Master Data COA**:
   - Buka: Master Data > COA
   - Edit setiap akun Kas/Bank (contoh: Kas, Bank BRI, Bank Mandiri)
   - Isi kolom **Saldo Awal** dengan nominal saldo awal Anda
   - Save

2. **Saldo akan otomatis terupdate**:
   - Setiap transaksi Penjualan (cash/transfer) → tambah saldo
   - Setiap transaksi Pembelian (cash/transfer) → kurangi saldo
   - Setiap Pembayaran Beban → kurangi saldo
   - Setiap Penggajian → kurangi saldo

### Rollback (Jika Ada Masalah):
Jika terjadi error, rollback migration:
```bash
php artisan migrate:rollback --step=1
```

Kemudian hubungi developer untuk investigasi lebih lanjut.

---

## 📝 Commits:
- `9e1c4605`: Fix foreign key constraint penjualans & pembelians
- `3fbd9cce`: Fix saldo di pembelian gunakan logika sama dengan Laporan Kas Bank
- `2b813ad4`: Fix Laporan Kas Bank prioritas saldo awal dari Master Data COA

---

## 🎯 Expected Results:

### Before:
- ❌ Error saat simpan penjualan: "foreign key constraint violation"
- ❌ Saldo di form pembelian tidak sama dengan Laporan Kas Bank
- ❌ Saldo Awal di Laporan Kas Bank kosong/salah

### After:
- ✅ Penjualan bisa disimpan tanpa error
- ✅ Saldo di form pembelian = Saldo Akhir di Laporan Kas Bank
- ✅ Saldo Awal di Laporan Kas Bank = saldo_awal di Master Data COA
- ✅ Uang Masuk/Keluar sesuai dengan transaksi pembelian/penjualan

---

**Tested by**: Kiro AI Assistant
**Date**: 01 Juli 2026
**Status**: ✅ Ready for Production
