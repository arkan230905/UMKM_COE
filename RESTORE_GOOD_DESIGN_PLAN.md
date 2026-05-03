# 🎨 RESTORE GOOD DESIGN PLAN

## 🎯 TUJUAN
Mengembalikan desain bagus dari commit **96c240b** sambil **MEMPERTAHANKAN** keamanan multi-tenant yang sudah ditambahkan.

---

## ✅ STRATEGI

### KEEP (Jangan di-restore):
- ✅ **Controllers** - Sudah ada keamanan `->where('user_id', auth()->id())`
- ✅ **Models** - Sudah ada auto-fill `user_id`
- ✅ **Migrations** - Sudah ada kolom `user_id` dan unique constraints
- ✅ **Observers** - Sudah ada logic multi-tenant
- ✅ **Services** - Sudah ada keamanan multi-tenant

### RESTORE (Kembalikan ke 96c240b):
- 🎨 **Views** (resources/views/**) - Desain bagus
- 🎨 **CSS** (public/css/**) - Styling bagus
- 🎨 **JS** (public/js/**) - Interaksi bagus

---

## 📋 FILES TO RESTORE

### Views yang Berubah (perlu di-restore):
```
resources/views/laporan/pelunasan-utang/index.blade.php
resources/views/laporan/pelunasan-utang/pdf.blade.php
resources/views/laporan/pembelian/index.blade.php ✅ DONE
resources/views/laporan/pembelian/invoice.blade.php
resources/views/laporan/pembelian/partials/pembelian-content.blade.php
resources/views/laporan/pembelian/partials/retur-content.blade.php
resources/views/laporan/retur/index.blade.php
resources/views/laporan/stok/export-single.blade.php
resources/views/laporan/stok/index.blade.php
resources/views/master-data/bahan-baku/index.blade.php
resources/views/master-data/bahan-pendukung/edit.blade.php
resources/views/master-data/bahan-pendukung/index.blade.php
resources/views/master-data/pegawai/create.blade.php
resources/views/transaksi/pelunasan-utang/index.blade.php
resources/views/transaksi/pembelian/index.blade.php
resources/views/transaksi/pembelian/partials/pembelian-content.blade.php
resources/views/transaksi/pembelian/partials/retur-content.blade.php
resources/views/transaksi/pembelian/preview-faktur.blade.php
resources/views/transaksi/pembelian/retur-create.blade.php
resources/views/transaksi/pembelian/show.blade.php
resources/views/transaksi/retur-pembelian/*.blade.php (semua file)
```

### CSS/JS yang Berubah (perlu dicek):
```
public/css/retur-pembelian.css (BARU - KEEP)
public/js/retur-pembelian.js (BARU - KEEP)
```

---

## 🚀 EXECUTION PLAN

### Step 1: Restore View Files (Batch 1 - Laporan)
```bash
git checkout 96c240b -- resources/views/laporan/pelunasan-utang/index.blade.php
git checkout 96c240b -- resources/views/laporan/pelunasan-utang/pdf.blade.php
git checkout 96c240b -- resources/views/laporan/pembelian/invoice.blade.php
git checkout 96c240b -- resources/views/laporan/pembelian/partials/pembelian-content.blade.php
git checkout 96c240b -- resources/views/laporan/pembelian/partials/retur-content.blade.php
git checkout 96c240b -- resources/views/laporan/retur/index.blade.php
git checkout 96c240b -- resources/views/laporan/stok/export-single.blade.php
git checkout 96c240b -- resources/views/laporan/stok/index.blade.php
```

### Step 2: Restore View Files (Batch 2 - Master Data)
```bash
git checkout 96c240b -- resources/views/master-data/bahan-baku/index.blade.php
git checkout 96c240b -- resources/views/master-data/bahan-pendukung/edit.blade.php
git checkout 96c240b -- resources/views/master-data/bahan-pendukung/index.blade.php
```

### Step 3: Restore View Files (Batch 3 - Transaksi Pembelian)
```bash
git checkout 96c240b -- resources/views/transaksi/pelunasan-utang/index.blade.php
git checkout 96c240b -- resources/views/transaksi/pembelian/index.blade.php
git checkout 96c240b -- resources/views/transaksi/pembelian/partials/pembelian-content.blade.php
git checkout 96c240b -- resources/views/transaksi/pembelian/partials/retur-content.blade.php
git checkout 96c240b -- resources/views/transaksi/pembelian/preview-faktur.blade.php
git checkout 96c240b -- resources/views/transaksi/pembelian/retur-create.blade.php
git checkout 96c240b -- resources/views/transaksi/pembelian/show.blade.php
```

### Step 4: Handle Retur Pembelian (SKIP - File baru untuk fitur baru)
File-file di `resources/views/transaksi/retur-pembelian/` adalah BARU (tidak ada di 96c240b), jadi KEEP.

### Step 5: Handle Pegawai Create (SPECIAL CASE)
File `resources/views/master-data/pegawai/create.blade.php` sudah dimodifikasi untuk fix kategori dropdown. 
**DECISION**: KEEP versi sekarang (sudah ada fix kategori + dynamic jabatan loading)

---

## ⚠️ SPECIAL CASES

### 1. Pegawai Create Form
**Status**: KEEP current version  
**Reason**: Sudah ada fix untuk kategori dropdown dan dynamic jabatan loading yang diminta user

### 2. Retur Pembelian Views
**Status**: KEEP all new files  
**Reason**: Fitur baru yang tidak ada di commit 96c240b

### 3. CSS/JS Retur
**Status**: KEEP  
**Reason**: File baru untuk fitur retur pembelian

---

## 📊 SUMMARY

**Total Files Changed**: 115 files  
**Controllers to KEEP**: 25 files (multi-tenant security)  
**Models to KEEP**: 13 files (auto-fill user_id)  
**Views to RESTORE**: ~30 files (good design)  
**New Files to KEEP**: ~20 files (new features)  

---

## ✅ VERIFICATION CHECKLIST

After restore:
- [ ] All views have good design from 96c240b
- [ ] All controllers still have `->where('user_id', auth()->id())`
- [ ] All models still have auto-fill `user_id`
- [ ] Pegawai create form still has kategori dropdown fix
- [ ] Retur pembelian feature still works
- [ ] Multi-tenant isolation still works (no data leakage)

---

## 🎉 EXPECTED RESULT

✅ Desain bagus dari commit 96c240b  
✅ Alur/logika bisnis yang benar  
✅ Keamanan multi-tenant yang kuat (anti bocor data)  
✅ Fitur baru (retur pembelian) tetap berfungsi  
✅ Fix kategori pegawai tetap berfungsi  

---

**Next Step**: Execute restoration commands!
