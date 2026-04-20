# COA Management Guide

## Overview
Panduan lengkap untuk mengelola Chart of Accounts (COA) dalam sistem UMKM.

## 🔧 Commands Available

### 1. Update COA Seeder
Membuat/update seeder berdasarkan data COA saat ini di database.

```bash
# Update seeder tanpa konfirmasi
php artisan coa:update-seeder --force

# Update seeder dengan backup
php artisan coa:update-seeder --backup

# Update seeder dengan konfirmasi
php artisan coa:update-seeder
```

### 2. Validate Payroll COA
Memvalidasi bahwa semua akun COA yang dibutuhkan untuk penggajian sudah ada.

```bash
# Validasi saja
php artisan coa:validate-payroll

# Validasi dan buat akun yang hilang
php artisan coa:validate-payroll --create-missing
```

### 3. Clean Duplicate COA
Membersihkan data COA yang duplikat, menjaga record tertua untuk setiap duplikat.

```bash
# Cek duplikat tanpa menghapus (dry run)
php artisan coa:clean-duplicates --dry-run

# Bersihkan duplikat secara otomatis
php artisan coa:clean-duplicates
```

### 4. Run COA Seeder
Menjalankan seeder untuk mengisi/update data COA.

```bash
# Jalankan seeder COA terbaru
php artisan db:seed --class=UpdatedCoaSeeder

# Jalankan semua seeder
php artisan db:seed
```

## 📋 Required COA for Payroll System

| Kode | Nama Akun | Tipe | Fungsi |
|------|-----------|------|--------|
| 52 | BIAYA TENAGA KERJA LANGSUNG (BTKL) | Expense | Gaji dasar BTKL |
| 54 | BOP TENAGA KERJA TIDAK LANGSUNG | Expense | Gaji dasar BTKTL |
| 513 | Beban Tunjangan | Expense | Tunjangan (transport, konsumsi, dll) |
| 514 | Beban Asuransi | Expense | Asuransi/BPJS |
| 515 | Beban Bonus | Expense | Bonus karyawan |
| 516 | Potongan Gaji | Expense | Potongan gaji (contra account) |
| 111 | Kas Bank | Asset | Pembayaran via bank |
| 112 | Kas | Asset | Pembayaran tunai |

## 🔄 Workflow untuk Update COA

### Skenario 1: Menambah Akun COA Baru
1. Tambah akun COA melalui interface admin
2. Update seeder: `php artisan coa:update-seeder --force`
3. Commit perubahan seeder ke repository

### Skenario 2: Setup Environment Baru
1. Clone repository
2. Setup database
3. Jalankan migration: `php artisan migrate`
4. Jalankan seeder: `php artisan db:seed`
5. Validasi COA penggajian: `php artisan coa:validate-payroll`

### Skenario 3: Restore/Backup COA
1. Buat backup: `php artisan coa:update-seeder --backup`
2. File backup tersimpan di `storage/app/coa_backup_*.sql`
3. Untuk restore, import file SQL ke database

## 📁 File Locations

```
database/seeders/
├── UpdatedCoaSeeder.php          # Seeder COA terbaru (auto-generated)
├── DatabaseSeeder.php            # Main seeder (includes UpdatedCoaSeeder)
└── CoaTemplateSeeder.php         # Template COA untuk user baru

app/Console/Commands/
├── UpdateCoaFromCurrent.php      # Command untuk update seeder
└── ValidatePayrollCoa.php        # Command untuk validasi COA penggajian

storage/app/
└── coa_backup_*.sql              # File backup COA (auto-generated)
```

## ⚠️ Important Notes

### Safety
- **SELALU** buat backup sebelum menjalankan seeder dengan `Coa::truncate()`
- Gunakan `updateOrCreate()` untuk menghindari duplikasi data
- Test di environment development sebelum production

### Best Practices
1. **Update seeder setiap kali ada perubahan COA**
2. **Commit seeder ke repository** agar tim lain mendapat update
3. **Validasi COA penggajian** setelah perubahan besar
4. **Buat backup berkala** untuk recovery

### Troubleshooting

#### Problem: COA tidak ditemukan saat penggajian
```bash
# Solusi: Validasi dan buat akun yang hilang
php artisan coa:validate-payroll --create-missing
```

#### Problem: Seeder tidak up-to-date
```bash
# Solusi: Update seeder dari database saat ini
php artisan coa:update-seeder --force
```

#### Problem: Data COA hilang
```bash
# Solusi: Restore dari backup atau jalankan seeder
php artisan db:seed --class=UpdatedCoaSeeder
```

#### Problem: Data COA duplikat
```bash
# Solusi: Cek dan bersihkan duplikat
php artisan coa:clean-duplicates --dry-run  # Cek dulu
php artisan coa:clean-duplicates             # Bersihkan
```

## 🎯 Integration dengan Sistem Penggajian

Sistem penggajian secara otomatis akan:
1. **Validasi COA** sebelum membuat jurnal
2. **Error handling** jika akun tidak ditemukan
3. **Logging** untuk tracking penggunaan akun
4. **Balance checking** untuk memastikan jurnal benar

### Error Messages
- `COA tidak ditemukan: 513 (Beban Tunjangan)` → Jalankan `php artisan coa:validate-payroll --create-missing`
- `Akun beban gaji tidak ditemukan` → Pastikan akun 52 (BTKL) atau 54 (BOP) ada

## 📊 Monitoring

### Check COA Status
```bash
# Lihat semua akun COA
php artisan tinker --execute="App\Models\Coa::count()"

# Validasi akun penggajian
php artisan coa:validate-payroll

# Update seeder jika perlu
php artisan coa:update-seeder --force
```

---

**Last Updated:** 2026-04-20  
**Version:** 1.0  
**Maintainer:** System Administrator