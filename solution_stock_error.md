# Solution: Insufficient Stock Error

## Problem
You're getting this error when trying to sell product #2 (Ayam Ketumbar):
```
RuntimeException - Insufficient stock for product#2 (need 5, available 0)
```

## Root Cause
Product #2 (Ayam Ketumbar) has 0 stock available in the stock layers system, but you're trying to sell 5 units.

## Solution Options

### Option 1: Add Initial Stock (Quick Fix)
Run this SQL in your database (phpMyAdmin or similar):

```sql
-- Add stock layer for 20 units
INSERT INTO stock_layers (item_type, item_id, qty, remaining_qty, unit_cost, ref_type, ref_id, created_at, updated_at)
VALUES ('product', 2, 20, 20, 35000, 'initial_stock', 0, NOW(), NOW());

-- Add stock movement record
INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at)
VALUES ('product', 2, CURDATE(), 'in', 20, 'Pcs', 35000, 700000, 'initial_stock', 0, NOW(), NOW());

-- Update product master data
UPDATE produks SET stok = 20, updated_at = NOW() WHERE id = 2;
```

### Option 2: Create Production Record (Proper Way)
1. Go to **Transaksi → Produksi → Tambah Produksi**
2. Select "Ayam Ketumbar" as the product
3. Enter quantity to produce (e.g., 20 units)
4. Complete the production process
5. This will automatically create stock layers

### Option 3: Check Existing Production
If you believe you already produced this item:
1. Go to **Transaksi → Produksi** 
2. Check if there are any production records for Ayam Ketumbar
3. If status is "Draft", complete the production process
4. If completed but no stock, there might be a system issue

## After Fixing
Once you add stock using any of the above methods:
1. The sales page should work normally
2. You'll be able to sell up to the available quantity
3. Stock will be automatically reduced after each sale

## Prevention
To avoid this in the future:
1. Always create production records before selling
2. Monitor stock levels in the product master data
3. Set up minimum stock alerts if available

## Verification
After applying the fix, verify by:
1. Checking **Master Data → Produk** - stock should show 20
2. Trying the sales transaction again - should work
3. Checking **Laporan → Stok** - should show the stock movement