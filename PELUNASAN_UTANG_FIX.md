# Pelunasan Utang - Fix Documentation

## Problem Summary
Saat melakukan pelunasan utang (debt payment), sistem mengalami error karena:
1. COA 2101 (Hutang Usaha) tidak ada di database
2. Ada duplikasi COA dengan nama "Hutang Usaha" (kode 210 dan 2101)
3. Form tidak menampilkan kode akun dengan jelas, sehingga user tidak tahu akun mana yang valid

## Solutions Implemented

### 1. Removed Duplicate COA
- **Deleted**: COA ID 41 (Kode: 210 - Hutang Usaha)
- **Kept**: COA ID 100 (Kode: 2101 - Hutang Usaha)
- **Reason**: JournalService hardcodes '2101' for Hutang Usaha, so we need to keep only this code

**Script**: `clean_duplicate_hutang_usaha.php`

### 2. Added Missing COA 2101
- **Migration**: `2026_04_20_143241_add_coa_2101_hutang_usaha.php`
- **Details**:
  - Kode: 2101
  - Nama: Hutang Usaha
  - Tipe: Liability
  - Kategori: Liability
  - Saldo Normal: Debit
  - Keterangan: Hutang Usaha - Digunakan untuk jurnal pembelian kredit

### 3. Added Missing COA 1130
- **Details**:
  - Kode: 1130
  - Nama: PPN Masukan
  - Tipe: Asset
  - Kategori: Asset
  - Saldo Normal: Debit
  - Keterangan: PPN Masukan dari pembelian
- **Reason**: JournalService uses this for PPN entries in purchase journals

**Script**: `add_missing_coas.php`

### 4. Updated Form Display
- **File**: `resources/views/transaksi/pelunasan-utang/create.blade.php`
- **Changes**:
  - Changed format from `kode - nama` to `[kode] nama`
  - Makes it clearer which account code is being selected
  - Helps prevent errors from selecting wrong accounts

### 5. Updated Controller
- **File**: `app/Http/Controllers/PelunasanUtangController.php`
- **Changes**:
  - Updated COA filter to only include valid liability accounts: 2101, 211, 212
  - Removed invalid codes: 21, 210
  - Added logging for missing accounts

### 6. Updated Seeder
- **File**: `database/seeders/UpdatedCoaSeeder.php`
- **Changes**:
  - Added COA 2101 (Hutang Usaha)
  - Total accounts increased from 90 to 91

## Testing

### Test Script: `test_pelunasan_utang.php`
Successfully tested:
- ✓ COA 2101 exists and is accessible
- ✓ Pelunasan utang can be created
- ✓ Journal entries are created correctly
- ✓ Purchase status is updated properly
- ✓ No "COA tidak ditemukan" errors

### Test Results
```
✅ PELUNASAN UTANG TEST SUCCESSFUL!

Final Status:
  - Kode Transaksi: PU-20260420-0001
  - Jumlah Pembayaran: Rp 500.000
  - Akun Kas: [111] Kas Bank
  - COA Pelunasan: [2101] Hutang Usaha
```

## Files Modified/Created

### Created
- `app/Console/Commands/CleanDuplicateHutangUsaha.php` - Command to clean duplicates
- `database/migrations/2026_04_20_143241_add_coa_2101_hutang_usaha.php` - Migration for COA 2101
- `clean_duplicate_hutang_usaha.php` - Script to remove duplicate COA
- `add_missing_coas.php` - Script to add missing COAs
- `test_pelunasan_utang.php` - Test script for pelunasan utang

### Modified
- `app/Http/Controllers/PelunasanUtangController.php` - Updated COA filter
- `resources/views/transaksi/pelunasan-utang/create.blade.php` - Updated form display
- `database/seeders/UpdatedCoaSeeder.php` - Added COA 2101

## COA Codes Used in System

### For Pelunasan Utang
- **2101**: Hutang Usaha (Liability) - Main account for debt payment
- **211**: Hutang Gaji (Liability) - For salary debt
- **212**: PPN Keluaran (Liability) - For output tax

### For Purchase Journals
- **1130**: PPN Masukan (Asset) - Input tax
- **511**: Biaya Kirim/Angkut (Expense) - Shipping cost
- **1104**: Persediaan Bahan Baku (Asset) - Raw material inventory
- **1107**: Persediaan Bahan Pendukung (Asset) - Supporting material inventory
- **111**: Kas Bank (Asset) - Bank cash
- **112**: Kas (Asset) - Cash

## How to Use

### For Users
1. Go to Transaksi > Pelunasan Utang
2. Select a purchase with remaining debt
3. Choose payment account (e.g., [111] Kas Bank)
4. Choose liability account (e.g., [2101] Hutang Usaha)
5. Enter payment amount
6. Submit form

### For Developers
If you encounter "COA tidak ditemukan" errors:
1. Check which COA code is missing
2. Add it to `UpdatedCoaSeeder.php`
3. Create a migration to add it to database
4. Run migration: `php artisan migrate`

## Prevention

To prevent similar issues in the future:
1. Always check if COA exists before using it in JournalService
2. Use the seeder to maintain all required COAs
3. Display COA codes in forms so users can verify
4. Add validation to ensure selected accounts exist

## Related Issues
- Issue: "COA dengan kode 2101 tidak ditemukan"
- Issue: "COA dengan kode 1130 tidak ditemukan"
- Issue: Duplicate Hutang Usaha accounts in database
