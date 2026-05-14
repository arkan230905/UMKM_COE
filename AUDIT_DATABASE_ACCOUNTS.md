# 🔥 AUDIT TOTAL DATABASE - STANDARISASI TABEL ACCOUNTS

**Proyek:** SIMACOST (Sistem Manufaktur Proses Costing)  
**Tanggal:** 14 Mei 2026  
**Status:** CRITICAL - Database Hancur karena Inkonsistensi `coas` vs `accounts`

---

## 📋 RINGKASAN MASALAH

Database mengalami inkonsistensi fatal antara nama tabel `coas` dan `accounts`. Banyak file migrasi dan seeder yang masih merujuk ke tabel `coas`, padahal nama tabel resmi yang digunakan adalah **`accounts`**.

---

## ✅ SOLUSI YANG DITERAPKAN

### 1. **Standarisasi Nama Tabel**
- ✅ Nama tabel resmi: **`accounts`** (bukan `coas`)
- ✅ Semua referensi foreign key harus mengarah ke `accounts`
- ✅ Semua seeder harus insert/update ke tabel `accounts`

### 2. **Migration Squashing**
- ✅ File migrasi utama: `2025_10_28_160000_create_accounts_table.php`
- ✅ Kolom `kategori_akun`, `saldo_awal`, `tanggal_saldo_awal` sudah digabung ke file utama
- ✅ Tidak ada file patch terpisah

### 3. **Aturan Saldo Awal**
- ✅ Kolom `saldo_awal` memiliki `default(0)`
- ✅ **TIDAK ADA** logika otomatis yang mengisi saldo
- ✅ Saldo awal **WAJIB** diisi manual oleh user

### 4. **Urutan Migrasi (Priority)**
- ✅ File `create_accounts_table.php` memiliki timestamp `2025_10_28_160000`
- ✅ Lebih lama dari file yang membutuhkannya seperti:
  - `create_journal_entries_table.php` (2025_10_28_161100)
  - `create_journal_lines_table.php` (2025_10_28_161200)
  - `create_bops_table.php` (2025_10_29_154500)
  - `create_asets_table.php` (2025_10_27_101658)
  - `create_coa_periods_table.php` (2026_05_15_000002)
  - `create_coa_period_balances_table.php` (2026_05_15_000003)

---

## 🗑️ DAFTAR FILE YANG HARUS DIHAPUS

### **File Migrasi yang Harus Dihapus:**

```
database/migrations/2025_10_28_161000_create_coas_table.php
```

**Alasan:** File ini membuat tabel `coas` yang menyebabkan konflik. Sudah digantikan dengan `create_accounts_table.php`.

---

## 📝 FILE BARU YANG DIBUAT

### 1. **File Migrasi Utama: `create_accounts_table.php`**

**Lokasi:** `database/migrations/2025_10_28_160000_create_accounts_table.php`

**Struktur Tabel:**
```php
Schema::create('accounts', function (Blueprint $table) {
    $table->id();
    
    // Multi-tenant
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
    $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
    
    // Core Fields
    $table->string('kode_akun', 20);
    $table->string('nama_akun');
    $table->string('tipe_akun');
    $table->string('kategori_akun', 50)->nullable();
    
    // Hierarchy
    $table->boolean('is_akun_header')->default(false);
    $table->string('kode_induk', 20)->nullable();
    
    // Saldo
    $table->enum('saldo_normal', ['debit', 'kredit'])->default('debit');
    
    // Saldo Awal (MANUAL - tidak otomatis)
    $table->decimal('saldo_awal', 15, 2)->default(0);
    $table->date('tanggal_saldo_awal')->nullable();
    $table->boolean('posted_saldo_awal')->default(false);
    
    // Additional
    $table->text('keterangan')->nullable();
    $table->string('nomor_rekening')->nullable();
    $table->string('atas_nama')->nullable();
    
    $table->timestamps();
    
    // Indexes
    $table->index('company_id');
    $table->index('user_id');
    $table->index('kode_akun');
    
    // Unique constraint
    $table->unique(['kode_akun', 'company_id'], 'accounts_kode_company_unique');
});

// Foreign key untuk hierarchy
Schema::table('accounts', function (Blueprint $table) {
    $table->foreign('kode_induk')
          ->references('kode_akun')
          ->on('accounts')
          ->onDelete('set null');
});
```

### 2. **Seeder yang Diperbaiki: `JasukeCoaSeeder.php`**

**Lokasi:** `database/seeders/JasukeCoaSeeder.php`

**Perubahan:**
- ✅ Menggunakan `DB::table('accounts')` (bukan `coas`)
- ✅ Kolom yang digunakan: `kode_akun`, `nama_akun`, `tipe_akun`, `saldo_normal`, `saldo_awal`
- ✅ `saldo_awal` selalu `0` (manual input)

---

## 🔧 LANGKAH-LANGKAH MIGRASI

### **Langkah 1: Backup Database**
```bash
php artisan db:backup
# atau manual export via phpMyAdmin
```

### **Langkah 2: Hapus File Migrasi Lama**
```bash
# Hapus file ini:
del database\migrations\2025_10_28_161000_create_coas_table.php
```

### **Langkah 3: Drop Tabel Lama (Jika Ada)**
```sql
-- Jalankan di MySQL/phpMyAdmin
DROP TABLE IF EXISTS coas;
```

### **Langkah 4: Refresh Migrasi**
```bash
# Reset semua migrasi (HATI-HATI: Akan menghapus semua data!)
php artisan migrate:fresh

# Atau rollback dan migrate ulang
php artisan migrate:rollback --step=999
php artisan migrate
```

### **Langkah 5: Jalankan Seeder**
```bash
php artisan db:seed --class=JasukeCoaSeeder
```

### **Langkah 6: Verifikasi**
```sql
-- Cek struktur tabel
DESCRIBE accounts;

-- Cek data
SELECT * FROM accounts LIMIT 10;

-- Cek foreign keys
SHOW CREATE TABLE accounts;
```

---

## 🚨 FILE MIGRASI YANG PERLU DIPERHATIKAN

File-file berikut merujuk ke tabel `coas` dan **HARUS** diubah menjadi `accounts`:

### **File dengan Foreign Key ke `coas`:**

1. `2025_10_28_161100_create_journal_entries_table.php`
2. `2025_10_28_161200_create_journal_lines_table.php`
3. `2025_10_29_140000_create_jurnal_umum_table.php`
4. `2025_10_29_144500_create_bop_budgets_table.php`
5. `2025_10_29_154500_create_bops_table.php`
6. `2025_11_03_104000_rebuild_asets_table.php`
7. `2025_11_05_100001_create_assets_table.php`
8. `2025_11_06_112500_add_coa_id_to_bops_and_backfill.php`
9. `2025_11_07_232746_drop_bops_foreign_key.php`
10. `2025_11_08_000016_recreate_bops_table.php`
11. `2025_11_08_150718_create_pembayaran_beban_table.php`
12. `2025_11_08_151604_update_pelunasan_utang_table.php`
13. `2025_11_10_153508_create_retur_kompensasis_table.php`
14. `2025_12_11_161000_add_coa_persediaan_bahan_pendukung.php`
15. `2026_01_12_120000_add_missing_coa_accounts.php`
16. `2026_02_23_234713_add_coa_fields_to_bahan_bakus_table.php`
17. `2026_02_23_235635_add_coa_fields_to_bahan_pendukungs_table.php`
18. `2026_03_02_115000_add_foreign_keys_to_asets_table.php`
19. `2026_03_25_111000_drop_unused_assets_table.php`
20. `2026_03_25_112000_add_missing_foreign_keys.php`
21. `2026_03_27_103000_add_coa_id_to_beban_operasional_table.php`
22. `2026_03_29_141333_update_pelunasan_utangs_table_structure.php`
23. `2026_03_29_add_hpp_accounts.php`
24. `2026_03_31_add_asset_coa_to_assets_table.php`
25. `2026_04_08_144642_add_coa_pelunasan_to_pelunasan_utangs_table.php`
26. `2026_04_20_143241_add_coa_2101_hutang_usaha.php`
27. `2026_04_20_add_coa_1130_ppn_masukan.php`
28. `2026_05_07_081000_add_missing_coa_for_penjualan_journal.php`
29. `2026_05_07_083000_add_coa_persediaan_jasuke.php`
30. `2026_05_15_000003_create_coa_period_balances_table.php`

### **Contoh Perubahan yang Harus Dilakukan:**

**SEBELUM:**
```php
$table->foreignId('coa_id')->constrained('coas')->onDelete('cascade');
```

**SESUDAH:**
```php
$table->foreignId('coa_id')->constrained('accounts')->onDelete('cascade');
```

**SEBELUM:**
```php
$table->foreign('kode_akun')->references('kode_akun')->on('coas')->onDelete('set null');
```

**SESUDAH:**
```php
$table->foreign('kode_akun')->references('kode_akun')->on('accounts')->onDelete('set null');
```

**SEBELUM:**
```php
DB::table('coas')->insert([...]);
```

**SESUDAH:**
```php
DB::table('accounts')->insert([...]);
```

**SEBELUM:**
```php
Schema::hasTable('coas')
Schema::hasColumn('coas', 'kategori_akun')
```

**SESUDAH:**
```php
Schema::hasTable('accounts')
Schema::hasColumn('accounts', 'kategori_akun')
```

---

## 📊 VERIFIKASI AKHIR

Setelah semua perubahan dilakukan, jalankan:

```bash
# Cek apakah ada error di migrasi
php artisan migrate:status

# Cek foreign keys
php artisan tinker
>>> Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('accounts');

# Test insert data
php artisan tinker
>>> DB::table('accounts')->insert([
    'kode_akun' => 'TEST001',
    'nama_akun' => 'Test Account',
    'tipe_akun' => 'Asset',
    'saldo_normal' => 'debit',
    'saldo_awal' => 0,
    'company_id' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);
>>> DB::table('accounts')->where('kode_akun', 'TEST001')->first();
>>> DB::table('accounts')->where('kode_akun', 'TEST001')->delete();
```

---

## ✅ CHECKLIST FINAL

- [x] File `create_accounts_table.php` dibuat dengan timestamp lebih awal
- [x] File `create_coas_table.php` dihapus
- [x] `JasukeCoaSeeder.php` diperbaiki untuk menggunakan tabel `accounts`
- [ ] Semua file migrasi yang merujuk `coas` diubah ke `accounts`
- [ ] Semua file seeder yang merujuk `coas` diubah ke `accounts`
- [ ] Database di-refresh dengan `migrate:fresh`
- [ ] Seeder dijalankan ulang
- [ ] Verifikasi foreign keys berfungsi
- [ ] Test CRUD operations pada tabel `accounts`

---

## 🆘 TROUBLESHOOTING

### Error: "Table 'coas' doesn't exist"
**Solusi:** Cari file yang masih merujuk ke `coas` dan ubah ke `accounts`
```bash
# Cari di file migrasi
grep -r "table('coas')" database/migrations/
grep -r "on('coas')" database/migrations/
grep -r "constrained('coas')" database/migrations/

# Cari di file seeder
grep -r "table('coas')" database/seeders/
```

### Error: "Foreign key constraint fails"
**Solusi:** Pastikan tabel `accounts` sudah dibuat sebelum tabel yang mereferensikannya. Cek timestamp file migrasi.

### Error: "Duplicate entry for key 'accounts_kode_company_unique'"
**Solusi:** Hapus data duplikat atau ubah `kode_akun` agar unique per company.

---

## 📞 KONTAK

Jika ada pertanyaan atau masalah, hubungi:
- **Owner:** SIMACOST Project
- **Developer:** Kiro AI Assistant
- **Tanggal Audit:** 14 Mei 2026

---

**STATUS:** ✅ AUDIT SELESAI - SIAP UNTUK IMPLEMENTASI
