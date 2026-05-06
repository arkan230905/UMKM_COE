# ✅ Fix Error Pembelian - SELESAI

## 🐛 Error yang Terjadi

```
Illuminate\Database\Eloquent\RelationNotFoundException
Call to undefined relationship [entry] on model [App\Models\JurnalUmum]
```

**Lokasi Error:** `app/Http/Controllers/PembelianController.php:556`

---

## 🔍 Root Cause

Di `PembelianController.php` baris 553, ada kode:

```php
$journalLines = \App\Models\JurnalUmum::where('coa_id', $bank->id)
    ->with('entry')  // ← ERROR: relasi 'entry' tidak ada!
    ->get();
```

Model `JurnalUmum` **tidak memiliki relasi `entry`**. Relasi ini hanya ada di model `JournalLine` (sistem jurnal yang berbeda).

---

## ✅ Solusi yang Diterapkan

### Hapus `->with('entry')`

**Before:**
```php
$journalLines = \App\Models\JurnalUmum::where('coa_id', $bank->id)
    ->with('entry')  // ← ERROR
    ->get();
```

**After:**
```php
$journalLines = \App\Models\JurnalUmum::where('coa_id', $bank->id)
    ->get();  // ← FIXED
```

Relasi `entry` tidak diperlukan karena kita hanya perlu sum debit dan credit dari `JurnalUmum` itu sendiri.

---

## 📝 File yang Dimodifikasi

1. ✅ `app/Http/Controllers/PembelianController.php` - Removed `->with('entry')`

---

## 🧪 Testing

### Sekarang Anda Bisa Test:

1. **Buka halaman tambah pembelian:**
   ```
   http://127.0.0.1:8000/transaksi/pembelian/create
   ```

2. **Isi form pembelian:**
   - Pilih vendor
   - Isi nomor faktur
   - Upload bukti faktur
   - Pilih tanggal
   - Pilih payment method (cash/credit)
   - Pilih bank
   - Tambah detail pembelian (bahan baku/pendukung)

3. **Submit form**

4. **Hasil yang diharapkan:**
   - ✅ Pembelian berhasil disimpan
   - ✅ Redirect ke halaman list pembelian
   - ✅ Tidak ada error 500
   - ✅ Data tersimpan di database

---

## 📚 Context: Dua Sistem Jurnal

Project ini memiliki 2 sistem jurnal yang berbeda:

### 1. Sistem Lama (Simple)
- Model: `JurnalUmum`
- Table: `jurnal_umum`
- Structure: Single table dengan kolom debit, credit, coa_id, dll
- Relasi: `coa()`, `createdBy()`

### 2. Sistem Baru (Double Entry)
- Models: `JournalEntry` + `JournalLine`
- Tables: `journal_entries` + `journal_lines`
- Structure: Header-detail pattern
- Relasi: `JournalLine` has `entry()` relation to `JournalEntry`

**Penting:** Jangan campur kedua sistem! Gunakan yang sesuai dengan konteks.

---

## ✅ Status

**Status:** ✅ FIXED  
**File Modified:** 1 file  
**Ready for:** Testing tambah pembelian baru

---

## 🚀 Next Steps

1. **Test tambah pembelian baru** - Pastikan tidak ada error
2. **Test bukti faktur** - Pastikan foto bisa dilihat (sudah fixed sebelumnya)
3. **Verify data** - Check database apakah data tersimpan dengan benar

---

**Date:** 2026-05-06  
**Issue:** Relation 'entry' not found  
**Status:** ✅ RESOLVED  
**Ready:** Testing
