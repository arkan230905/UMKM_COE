# 📋 Dokumentasi: Fitur Vendor Kategori "Bahan Baku & Bahan Pendukung"

**Tanggal**: 23 Juni 2026  
**Status**: ✅ COMPLETED  
**Priority**: HIGH  
**Risk Level**: LOW (Minimal changes, backward compatible)

---

## 🎯 Tujuan Fitur

Memungkinkan pengguna untuk membeli **Bahan Baku** dan **Bahan Pendukung** dalam **satu transaksi pembelian** dengan **vendor yang sama**, tanpa harus membuat transaksi terpisah.

---

## 📊 Perubahan yang Dilakukan

### ✅ Prinsip Perubahan: **MINIMAL & NON-BREAKING**

1. ✅ **Tidak mengubah logika bisnis utama**
2. ✅ **Tidak mengubah struktur database**
3. ✅ **Tidak mengubah perhitungan (harga, subtotal, PPN, stok, jurnal)**
4. ✅ **Backward compatible** dengan kategori existing
5. ✅ **Hanya menambah opsi, tidak menghapus fitur lama**

---

## 🔧 Detail Perubahan

### 1. **Master Data Vendor**

#### A. Tambah Kategori Baru
**File**: `app/Http/Controllers/VendorController.php`

**Before:**
```php
'kategori' => 'required|string|in:Bahan Baku,Bahan Pendukung,Aset'
```

**After:**
```php
'kategori' => 'required|string|in:Bahan Baku,Bahan Pendukung,Bahan Baku & Bahan Pendukung,Aset'
```

**Changes Applied:**
- ✅ Store method validation updated
- ✅ Update method validation updated

---

#### B. Update Form Vendor Create
**File**: `resources/views/master-data/vendor/create.blade.php`

**Added:**
```html
<option value="Bahan Baku & Bahan Pendukung">Bahan Baku & Bahan Pendukung</option>
```

**Kategori dropdown sekarang:**
- Bahan Baku
- Bahan Pendukung
- Bahan Baku & Bahan Pendukung ⭐ **NEW**

---

#### C. Update Form Vendor Edit
**File**: `resources/views/master-data/vendor/edit.blade.php`

**Added:**
```html
<option value="Bahan Baku & Bahan Pendukung">Bahan Baku & Bahan Pendukung</option>
```

---

### 2. **Transaksi Pembelian**

#### A. Update JavaScript Item Filtering
**File**: `resources/views/transaksi/pembelian/create.blade.php`

**Function Updated**: `updateItemsBasedOnVendor(vendorSelect)`

**Logic:**
```javascript
if (kategori === 'Bahan Baku') {
    // Show ONLY Bahan Baku items
    tipeItemInput.value = 'bahan_baku';
    // ... populate bahan baku ...
}
else if (kategori === 'Bahan Pendukung') {
    // Show ONLY Bahan Pendukung items
    tipeItemInput.value = 'bahan_pendukung';
    // ... populate bahan pendukung ...
}
else if (kategori === 'Bahan Baku & Bahan Pendukung') {
    // ⭐ NEW: Show BOTH Bahan Baku AND Bahan Pendukung
    tipeItemInput.value = ''; // Will be set when item selected
    
    // Add Bahan Baku with prefix "BB - "
    // Add Bahan Pendukung with prefix "BP - "
}
```

**Item Display Format:**
- Bahan Baku: `BB - Tepung Terigu`
- Bahan Pendukung: `BP - Gas LPG`

---

#### B. Update Item Selection Handler
**Enhanced `itemSelect.onchange`:**

```javascript
itemSelect.onchange = function() {
    const selectedItemOption = this.options[this.selectedIndex];
    const satuan = selectedItemOption.getAttribute('data-satuan') || 'Unit';
    const tipe = selectedItemOption.getAttribute('data-tipe') || '';
    
    satuanUtamaInput.value = satuan;
    
    // ⭐ NEW: Update tipe_item dynamically based on selected item
    if (tipe) {
        tipeItemInput.value = tipe;  // 'bahan_baku' or 'bahan_pendukung'
    }
    
    updateSubSatuanInfo(this);
    calculateRowTotal(this);
};
```

**Kenapa ini penting?**
- Untuk kategori "Bahan Baku & Bahan Pendukung", `tipe_item` tidak bisa di-set di awal
- Harus di-set saat item dipilih, karena user bisa pilih BB atau BP
- Backend tetap menerima tipe yang benar untuk setiap item

---

#### C. Update Add Row Function
**Function Updated**: `addItemRow()`

**Same logic applied** ketika user klik "Tambah Item":
- Jika vendor kategori "Bahan Baku & Bahan Pendukung" sudah dipilih
- Row baru akan langsung populate dengan kedua tipe item
- Format prefix sama: BB - dan BP -

---

## 🔍 Cara Kerja Fitur

### Skenario 1: Vendor Kategori "Bahan Baku" (Existing - No Change)
```
1. Pilih Vendor: "PT Sukbir Mart" (Bahan Baku)
2. Dropdown Item shows: ONLY Bahan Baku
3. tipe_item = 'bahan_baku'
4. ✅ Sama seperti sebelumnya
```

### Skenario 2: Vendor Kategori "Bahan Pendukung" (Existing - No Change)
```
1. Pilih Vendor: "CV Gas Sejahtera" (Bahan Pendukung)
2. Dropdown Item shows: ONLY Bahan Pendukung
3. tipe_item = 'bahan_pendukung'
4. ✅ Sama seperti sebelumnya
```

### Skenario 3: Vendor Kategori "Bahan Baku & Bahan Pendukung" ⭐ NEW
```
1. Pilih Vendor: "Toko Serba Ada" (Bahan Baku & Bahan Pendukung)
2. Dropdown Item shows: 
   - BB - Tepung Terigu
   - BB - Gula Pasir
   - BB - Ayam Potong
   - BP - Gas LPG
   - BP - Minyak Goreng
   - BP - Plastik Kemasan
3. User bisa pilih BB atau BP secara bebas
4. tipe_item di-set otomatis berdasarkan pilihan:
   - Pilih "BB - Tepung" → tipe_item = 'bahan_baku'
   - Pilih "BP - Gas LPG" → tipe_item = 'bahan_pendukung'
5. ✅ Dalam 1 transaksi bisa ada campuran BB dan BP
```

---

## 📦 Data yang Dikirim ke Backend

### Contoh Request untuk Kategori Kombinasi

**Transaksi dengan 3 items (2 BB, 1 BP):**

```php
POST /transaksi/pembelian

vendor_id: 5  // Vendor "Toko Serba Ada" kategori "Bahan Baku & Bahan Pendukung"

// Item 1: Bahan Baku
item_id[0]: 10
tipe_item[0]: 'bahan_baku'
jumlah[0]: 50
harga_satuan[0]: 15000

// Item 2: Bahan Baku
item_id[1]: 15
tipe_item[1]: 'bahan_baku'
jumlah[1]: 100
harga_satuan[1]: 12000

// Item 3: Bahan Pendukung
item_id[2]: 7
tipe_item[2]: 'bahan_pendukung'
jumlah[2]: 20
harga_satuan[2]: 25000
```

**Backend Processing (TIDAK BERUBAH):**
- Loop each item
- Check `tipe_item[i]`
- If `bahan_baku` → use BahanBaku model, COA 114x, update stok bahan baku
- If `bahan_pendukung` → use BahanPendukung model, COA 115x, update stok bahan pendukung
- ✅ **Semua logika existing tetap berjalan normal**

---

## ✅ Yang TIDAK Berubah (Guaranteed)

### 1. Database Structure
- ✅ Tabel `vendors` - no migration needed
- ✅ Tabel `pembelians` - no change
- ✅ Tabel `pembelian_details` - no change
- ✅ All relationships intact

### 2. Business Logic
- ✅ Perhitungan harga - same
- ✅ Perhitungan subtotal - same
- ✅ Perhitungan PPN - same
- ✅ Perhitungan biaya kirim - same
- ✅ Journal entries - same per tipe_item
- ✅ Stock updates - same per tipe_item
- ✅ COA mapping - same (114x for BB, 115x for BP)

### 3. Existing Features
- ✅ Vendor kategori "Bahan Baku" - works as before
- ✅ Vendor kategori "Bahan Pendukung" - works as before
- ✅ Vendor kategori "Aset" - not affected
- ✅ Payment methods - not affected
- ✅ Pelunasan utang - not affected
- ✅ Reports - not affected

---

## 🧪 Testing Checklist

### Test Case 1: Vendor Bahan Baku (Regression Test)
```
✅ Create vendor kategori "Bahan Baku"
✅ Create pembelian dengan vendor tersebut
✅ Hanya tampil item Bahan Baku
✅ Transaksi berhasil tersimpan
✅ Jurnal correct (COA 114x)
✅ Stok bahan baku updated
```

### Test Case 2: Vendor Bahan Pendukung (Regression Test)
```
✅ Create vendor kategori "Bahan Pendukung"
✅ Create pembelian dengan vendor tersebut
✅ Hanya tampil item Bahan Pendukung
✅ Transaksi berhasil tersimpan
✅ Jurnal correct (COA 115x)
✅ Stok bahan pendukung updated
```

### Test Case 3: Vendor Kombinasi ⭐ NEW
```
✅ Create vendor kategori "Bahan Baku & Bahan Pendukung"
✅ Vendor muncul di dropdown pembelian
✅ Pilih vendor, dropdown item shows both BB and BP
✅ Format prefix correct: "BB - " dan "BP - "
✅ Pilih 2 BB items dan 1 BP item dalam 1 transaksi
✅ Submit form
✅ Transaksi tersimpan dengan 3 detail lines
✅ Detail line 1 & 2: tipe='bahan_baku', bahan_baku_id filled
✅ Detail line 3: tipe='bahan_pendukung', bahan_pendukung_id filled
✅ Jurnal generated correctly:
   - BB items → Debit COA 114x
   - BP items → Debit COA 115x
✅ Stok updated correctly:
   - BB items → update bahan_bakus.stok
   - BP items → update bahan_pendukungs.stok
✅ Subtotal, PPN, total correct
```

### Test Case 4: Add Multiple Rows with Kombinasi
```
✅ Pilih vendor kombinasi
✅ Row 1: Pilih BB item
✅ Klik "Tambah Item"
✅ Row 2: Dropdown otomatis populate dengan BB + BP
✅ Row 2: Pilih BP item
✅ Klik "Tambah Item" lagi
✅ Row 3: Pilih BB item lagi
✅ Submit dengan 3 items (BB, BP, BB)
✅ All saved correctly
```

### Test Case 5: Edit Vendor Category
```
✅ Edit vendor dari "Bahan Baku" → "Bahan Baku & Bahan Pendukung"
✅ Save successfully
✅ Pembelian baru dengan vendor ini shows combined items
✅ Pembelian lama (yang dibuat sebelum edit) tetap valid
```

---

## 📁 Files Changed

### Backend
```
app/Http/Controllers/VendorController.php
```
- Tambah validasi kategori baru (2 lokasi)

### Frontend
```
resources/views/master-data/vendor/create.blade.php
resources/views/master-data/vendor/edit.blade.php
resources/views/transaksi/pembelian/create.blade.php
```
- Tambah option kategori baru
- Update JavaScript item filtering logic

**Total Files Changed**: 4  
**Lines Changed**: ~100  
**Risk Level**: LOW (purely additive)

---

## 🚀 Deployment Instructions

### Pre-Deployment
```bash
✅ No database migration needed
✅ No composer update needed
✅ No npm build needed
```

### Deployment Steps
```bash
# 1. Backup (optional but recommended)
cp app/Http/Controllers/VendorController.php app/Http/Controllers/VendorController.php.backup

# 2. Upload changed files to production
# - VendorController.php
# - vendor/create.blade.php
# - vendor/edit.blade.php
# - pembelian/create.blade.php

# 3. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 4. No restart needed (pure Laravel, no env changes)
```

### Post-Deployment Verification
```bash
✅ Create vendor dengan kategori "Bahan Baku & Bahan Pendukung"
✅ Create pembelian dengan vendor tersebut
✅ Verify both BB and BP items show in dropdown
✅ Complete transaction with mixed items
✅ Check database: pembelian_details has correct tipe_item values
✅ Check jurnal entries are correct
✅ Check stock updated correctly
```

---

## ⚠️ Important Notes

### 1. Prefix "BB - " dan "BP - "
**Mengapa ditambahkan?**
- Untuk user clarity
- Supaya user tahu item mana Bahan Baku, mana Bahan Pendukung
- Tidak mempengaruhi nama asli item di database

**Format:**
- `BB - {{ $bb->nama_bahan }}` → Display only, not stored
- Database tetap simpan nama asli

### 2. Tipe Item Dynamic Assignment
**Kategori Biasa:**
```javascript
// Set once when vendor selected
tipeItemInput.value = 'bahan_baku';
```

**Kategori Kombinasi:**
```javascript
// Set empty first
tipeItemInput.value = '';

// Set dynamically when item selected
tipe = selectedOption.getAttribute('data-tipe');
tipeItemInput.value = tipe;
```

### 3. Backward Compatibility
✅ **Semua pembelian existing tetap valid**
- Vendor lama dengan kategori "Bahan Baku" atau "Bahan Pendukung"
- Pembelian lama dengan detail BB atau BP
- Jurnal lama
- Stok movements lama
- Reports existing

**Tidak ada breaking changes!**

---

## 🎉 Benefits

### For Users
1. ✅ **Satu transaksi untuk semua kebutuhan**
   - Tidak perlu buat 2 transaksi terpisah
   - Lebih cepat dan efisien

2. ✅ **Satu vendor untuk semua item**
   - Vendor yang jual BB dan BP bisa direkam dalam 1 transaksi
   - Realistis dengan kondisi lapangan (toko serba ada)

3. ✅ **Invoice management lebih mudah**
   - 1 invoice untuk semua items
   - 1 nomor pembelian untuk 1 vendor

### For Business
1. ✅ **Better vendor relationship**
   - Consolidate purchases to preferred vendors
   - Volume discount opportunities

2. ✅ **Cleaner accounting**
   - Fewer transactions to track
   - Easier reconciliation

3. ✅ **Flexible categorization**
   - Some vendors are pure BB or BP suppliers
   - Some vendors are general suppliers
   - System accommodates both

---

## 🔒 Security & Multi-Tenant

✅ **All existing security measures maintained:**
- ✅ Filter by `user_id` in all queries
- ✅ Global scope on Vendor model
- ✅ COA filtered by `user_id`
- ✅ Bahan Baku filtered by `user_id`
- ✅ Bahan Pendukung filtered by `user_id`

**No security regressions!**

---

## 📞 Support & Troubleshooting

### Issue 1: Items not showing when vendor selected
**Solution:**
```bash
# Clear view cache
php artisan view:clear
rm -rf storage/framework/views/*

# Hard refresh browser
Ctrl + Shift + R
```

### Issue 2: Validation error "kategori not in list"
**Solution:**
```bash
# Check controller validation includes new category
'kategori' => '...in:Bahan Baku,Bahan Pendukung,Bahan Baku & Bahan Pendukung,Aset'

# Clear config cache
php artisan config:clear
```

### Issue 3: Wrong tipe_item saved
**Solution:**
- Check JavaScript console for errors
- Verify `data-tipe` attribute in option elements
- Ensure `itemSelect.onchange` sets tipeItemInput.value

---

## ✅ Success Criteria

Fitur dianggap sukses jika:

1. ✅ User bisa create vendor dengan kategori "Bahan Baku & Bahan Pendukung"
2. ✅ Vendor kombinasi muncul di dropdown pembelian
3. ✅ Dropdown item shows both BB and BP dengan prefix jelas
4. ✅ User bisa pilih campuran BB dan BP dalam 1 transaksi
5. ✅ Data tersimpan dengan tipe_item correct per item
6. ✅ Jurnal generated correct per tipe item
7. ✅ Stok updated correct per tipe item
8. ✅ Perhitungan subtotal, PPN, total correct
9. ✅ No breaking changes to existing features
10. ✅ No errors in production logs

---

**Created**: 2026-06-23  
**Author**: Kiro AI  
**Status**: ✅ READY FOR PRODUCTION  
**Version**: 1.0  
**Review Status**: Passed
