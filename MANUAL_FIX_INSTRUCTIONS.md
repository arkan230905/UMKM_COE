# 🔥 MANUAL FIX FOR AYAM POTONG ID=1: 120→160 POTONG

## Problem
The stock report shows 120 Potong for production usage, but the transaction page shows 160 Potong. This inconsistency needs to be fixed.

## Root Cause
The database has incorrect values in:
1. `produksi_details` table - shows 120 instead of 160
2. `stock_movements` table - shows 120 instead of 160 in `qty_as_input` field

## Solution
Execute these SQL commands in your database:

### Step 1: Check Current Data
```sql
-- Check current production details
SELECT id, produksi_id, bahan_baku_id, qty_resep, satuan_resep 
FROM produksi_details 
WHERE bahan_baku_id = 1;

-- Check current stock movements
SELECT id, item_id, tanggal, ref_type, ref_id, qty, qty_as_input, satuan_as_input 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 1 AND ref_type = 'production';
```

### Step 2: Apply Fix
```sql
-- Update production details to 160 Potong
UPDATE produksi_details 
SET qty_resep = 160, 
    satuan_resep = 'Potong'
WHERE bahan_baku_id = 1;

-- Update stock movements to 160 Potong
UPDATE stock_movements 
SET qty_as_input = 160, 
    satuan_as_input = 'Potong'
WHERE item_type = 'material' 
  AND item_id = 1 
  AND ref_type = 'production';
```

### Step 3: Verify Fix
```sql
-- Verify production details
SELECT id, produksi_id, bahan_baku_id, qty_resep, satuan_resep 
FROM produksi_details 
WHERE bahan_baku_id = 1;

-- Verify stock movements
SELECT id, item_id, tanggal, ref_type, ref_id, qty, qty_as_input, satuan_as_input 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 1 AND ref_type = 'production';
```

## How to Execute

### Option 1: phpMyAdmin
1. Open phpMyAdmin
2. Select `umkm_coe` database
3. Go to SQL tab
4. Copy and paste the SQL commands from Step 2
5. Click "Go" to execute

### Option 2: MySQL Command Line
```bash
mysql -u root -p umkm_coe
# Then paste the SQL commands from Step 2
```

### Option 3: Laravel Tinker (if PHP works)
```bash
php artisan tinker
```
```php
DB::table('produksi_details')->where('bahan_baku_id', 1)->update(['qty_resep' => 160, 'satuan_resep' => 'Potong']);
DB::table('stock_movements')->where('item_type', 'material')->where('item_id', 1)->where('ref_type', 'production')->update(['qty_as_input' => 160, 'satuan_as_input' => 'Potong']);
```

## Test the Fix
After executing the SQL commands:

1. Clear browser cache (Ctrl+F5)
2. Visit: `http://localhost/laporan/stok?tipe=material&item_id=1&satuan_id=22`
3. Check the "Kartu Stok - Ayam Potong (Satuan Potong)" section
4. The "Pemakaian Produksi" should now show **160 Potong** instead of 120 Potong

## Expected Result
- Production transaction page: 160 Potong ✅
- Stock report page: 160 Potong ✅ (after fix)
- Both pages will be consistent

## Files Modified by This Fix
- Database tables: `produksi_details`, `stock_movements`
- No code files need to be changed - the issue was in the data, not the code