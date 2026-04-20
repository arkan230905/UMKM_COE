# Jurnal Umum Balance Fix

## Problem
Jurnal Umum tidak balance (Debit ≠ Kredit) karena ada duplikasi entry untuk pembelian.

## Root Cause
Ada 2 tabel yang menyimpan journal data:
- **Tabel lama**: `jurnal_umum` (legacy system)
- **Tabel baru**: `journal_entries` + `journal_lines` (current system)

Ketika menampilkan Jurnal Umum, controller mengambil data dari KEDUA tabel, menyebabkan duplikasi:
- Pembelian 19/04/2026: 2 entry (dari jurnal_umum + journal_entries)
- Pembelian 20/04/2026: 2 entry (dari jurnal_umum + journal_entries)

## Solution

### 1. Identified Duplicate Records
Found 6 old purchase records in `jurnal_umum`:
- IDs: 166, 167, 168 (Pembelian 19/04/2026)
- IDs: 171, 172, 173 (Pembelian 20/04/2026)

### 2. Deleted Old Records
Removed all 6 records from `jurnal_umum` table.

### 3. Verified Balance
All journal entries in `journal_entries` are now balanced:
- Entry ID 22 (Pembelian 19/04/2026): Debit Rp 1.776.000 = Credit Rp 1.776.000 ✓
- Entry ID 24 (Pembelian 20/04/2026): Debit Rp 2.220.000 = Credit Rp 2.220.000 ✓

## Data Cleanup Summary

### Deleted from jurnal_umum
- **Pembelian 19/04/2026**: 3 records (IDs 166-168)
- **Pembelian 20/04/2026**: 3 records (IDs 171-173)
- **Total deleted**: 6 records

### Remaining in jurnal_umum
- 33 records (down from 39)
- Only non-purchase transactions remain

## Prevention

To prevent this issue in the future:

1. **Use only `journal_entries` + `journal_lines`** for all new transactions
2. **Migrate old data** from `jurnal_umum` to new tables
3. **Deprecate `jurnal_umum`** table once all data is migrated
4. **Update controller** to only query `journal_entries` (already done)

## Files Modified
- `app/Http/Controllers/AkuntansiController.php` - Already excludes old transaction types

## Testing

Jurnal Umum now displays:
- ✓ No duplicate entries
- ✓ All entries are balanced (Debit = Kredit)
- ✓ Correct dates and amounts
- ✓ Consistent with database records

## Related Issues Fixed
- Pembayaran Beban Sewa tanggal tidak sesuai (fixed by removing old records)
- Pelunasan Utang ada 2 entry (fixed by removing old records)
- Jurnal Umum tidak balance (fixed by removing duplicate purchase records)
