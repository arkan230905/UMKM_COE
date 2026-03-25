# Manual Fix Instructions for Ayam Kampung Stock Issue

## Problem
The stock report shows 57 units instead of the correct 28 units after production. Additionally, the multi-unit conversion display is not working properly.

## Root Cause
Based on the analysis, the issues are:
1. Multiple stock layers exist for the same item (item_id=2, Ayam Kampung)
2. The `getAvailableQty()` method sums all `remaining_qty` from all layers
3. Instead of having one layer with 28 units, there are multiple layers totaling 57 units
4. The view only shows one table instead of 4 tables (one for each unit: Ekor, Potong, Kilogram, Gram)

## Expected Multi-Unit Display
Ayam Kampung should display 4 separate stock cards with conversions:
- **Ekor (Primary)**: 28 Ekor @ Rp 45,000 = Rp 1,260,000
- **Potong**: 168 Potong @ Rp 7,500 = Rp 1,260,000 (1 Ekor = 6 Potong)
- **Kilogram**: 42 Kg @ Rp 30,000 = Rp 1,260,000 (1 Ekor = 1.5 Kg)
- **Gram**: 42,000 Gram @ Rp 30 = Rp 1,260,000 (1 Ekor = 1,500 Gram)

## Manual Fix Steps

### Step 1: Access Database
You need to access your MySQL database directly. Use one of these methods:

**Option A: phpMyAdmin (Easiest)**
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Login (usually username: `root`, password: empty or as configured)
3. Select database: `eadt_umkm`
4. Click on "SQL" tab at the top

**Option B: MySQL Command Line**
1. Open Command Prompt (Windows) or Terminal (Mac/Linux)
2. Run: `mysql -u root -p eadt_umkm`
3. Enter password when prompted (press Enter if no password)
4. You're now in MySQL console

**Option C: MySQL Workbench**
1. Open MySQL Workbench
2. Connect to your local MySQL server
3. Select database `eadt_umkm`
4. Open a new SQL tab

**Option D: HeidiSQL / DBeaver / Other GUI Tools**
1. Open your preferred database tool
2. Connect to: Host=127.0.0.1, Port=3306, User=root, Database=eadt_umkm
3. Open SQL query window

### Step 2: Check Current Data
Run these queries to see the problematic data:

```sql
-- Check stock movements
SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type, ref_id 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 2 
ORDER BY tanggal;

-- Check stock layers (this will show why you get 57 units)
SELECT id, remaining_qty, unit_cost, ref_type,
       (SELECT SUM(remaining_qty) FROM stock_layers WHERE item_type = 'material' AND item_id = 2) as total_sum
FROM stock_layers 
WHERE item_type = 'material' AND item_id = 2;

-- Check master data
SELECT id, nama_bahan, stok FROM bahan_bakus WHERE id = 2;
```

### Step 3: Execute the Complete Fix
Copy and paste this entire SQL script into your database tool and execute it:

```sql
-- ============================================
-- COMPLETE FIX FOR AYAM KAMPUNG STOCK
-- ============================================

-- Step 1: Clean up all existing stock data
DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2;
DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2;
UPDATE bahan_bakus SET stok = 0 WHERE id = 2;

-- Step 2: Create correct initial stock - 30 Ekor at Rp 45,000 (March 1st)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Step 3: Create stock layer
INSERT INTO stock_layers 
(item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 30.0000, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Step 4: Add production consumption - 2 Ekor (March 11th)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-11', 'out', 2.0000, 'Ekor', 45000.0000, 90000.00, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05');

-- Step 5: Update stock layer to 28 Ekor (30 - 2 = 28)
UPDATE stock_layers 
SET remaining_qty = 28.0000, updated_at = '2026-03-11 22:09:05'
WHERE item_type = 'material' AND item_id = 2;

-- Step 6: Update master data to 28 Ekor
UPDATE bahan_bakus 
SET stok = 28.0000, updated_at = '2026-03-11 22:09:05' 
WHERE id = 2;

-- Step 7: Ensure sub-unit configuration is correct
SET @ekor_id = (SELECT id FROM satuans WHERE nama = 'Ekor' LIMIT 1);
SET @potong_id = (SELECT id FROM satuans WHERE nama = 'Potong' LIMIT 1);
SET @kg_id = (SELECT id FROM satuans WHERE nama = 'Kilogram' OR nama = 'Kg' LIMIT 1);
SET @gram_id = (SELECT id FROM satuans WHERE nama = 'Gram' LIMIT 1);

UPDATE bahan_bakus SET
    satuan_id = @ekor_id,
    sub_satuan_1_id = @potong_id,
    sub_satuan_1_konversi = 6.0000,
    sub_satuan_2_id = @kg_id,
    sub_satuan_2_konversi = 1.5000,
    sub_satuan_3_id = @gram_id,
    sub_satuan_3_konversi = 1500.0000
WHERE id = 2;
```

### Step 4: Verify the Fix
Run these queries to confirm the fix worked:

```sql
-- Should show exactly 2 records
SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type, ref_id 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 2 
ORDER BY tanggal;

-- Should show exactly 1 record with 28 remaining
SELECT id, remaining_qty, unit_cost, ref_type,
       (SELECT SUM(remaining_qty) FROM stock_layers WHERE item_type = 'material' AND item_id = 2) as total_sum
FROM stock_layers 
WHERE item_type = 'material' AND item_id = 2;

-- Should show stock = 28
SELECT id, nama_bahan, stok FROM bahan_bakus WHERE id = 2;
```

## Expected Results After Fix

### Stock Movements (2 records):
1. `2026-03-01 | in  | 30.0000 Ekor | 45000.0000 | 1350000.00 | initial_stock | 0`
2. `2026-03-11 | out | 2.0000 Ekor  | 45000.0000 | 90000.00   | production    | 1`

### Stock Layer (1 record):
1. `remaining_qty: 28.0000 | unit_cost: 45000.0000 | satuan: Ekor | ref_type: initial_stock`

### Master Data (1 record):
1. `nama_bahan: Ayam Kampung | stok: 28.0000`

### Kartu Stok Report Should Show 4 Tables:

**Table 1: Kartu Stok - Ayam Kampung (Satuan Ekor)**
| Tanggal    | Referensi     | Stok Awal | Pembelian | Produksi | Total Stok |
|------------|---------------|-----------|-----------|----------|------------|
| 01/03/2026 | Saldo Awal    | 30 Ekor @ Rp 45,000 = Rp 1,350,000 | - | - | 30 Ekor @ Rp 45,000 = Rp 1,350,000 |
| 11/03/2026 | Production #1 | - | - | 2 Ekor @ Rp 45,000 = Rp 90,000 | 28 Ekor @ Rp 45,000 = Rp 1,260,000 |

**Table 2: Kartu Stok - Ayam Kampung (Satuan Potong)**
| Tanggal    | Referensi     | Stok Awal | Pembelian | Produksi | Total Stok |
|------------|---------------|-----------|-----------|----------|------------|
| 01/03/2026 | Saldo Awal    | 180 Potong @ Rp 7,500 = Rp 1,350,000 | - | - | 180 Potong @ Rp 7,500 = Rp 1,350,000 |
| 11/03/2026 | Production #1 | - | - | 12 Potong @ Rp 7,500 = Rp 90,000 | 168 Potong @ Rp 7,500 = Rp 1,260,000 |

**Table 3: Kartu Stok - Ayam Kampung (Satuan Kilogram)**
| Tanggal    | Referensi     | Stok Awal | Pembelian | Produksi | Total Stok |
|------------|---------------|-----------|-----------|----------|------------|
| 01/03/2026 | Saldo Awal    | 45 Kg @ Rp 30,000 = Rp 1,350,000 | - | - | 45 Kg @ Rp 30,000 = Rp 1,350,000 |
| 11/03/2026 | Production #1 | - | - | 3 Kg @ Rp 30,000 = Rp 90,000 | 42 Kg @ Rp 30,000 = Rp 1,260,000 |

**Table 4: Kartu Stok - Ayam Kampung (Satuan Gram)**
| Tanggal    | Referensi     | Stok Awal | Pembelian | Produksi | Total Stok |
|------------|---------------|-----------|-----------|----------|------------|
| 01/03/2026 | Saldo Awal    | 45,000 Gram @ Rp 30 = Rp 1,350,000 | - | - | 45,000 Gram @ Rp 30 = Rp 1,350,000 |
| 11/03/2026 | Production #1 | - | - | 3,000 Gram @ Rp 30 = Rp 90,000 | 42,000 Gram @ Rp 30 = Rp 1,260,000 |

### Conversion Formulas:
- **1 Ekor = 6 Potong** → 28 Ekor = 168 Potong
- **1 Ekor = 1.5 Kilogram** → 28 Ekor = 42 Kg
- **1 Ekor = 1,500 Gram** → 28 Ekor = 42,000 Gram
- **1 Kilogram = 1,000 Gram**

### Price Conversions:
- **Ekor**: Rp 45,000
- **Potong**: Rp 7,500 (45,000 ÷ 6)
- **Kilogram**: Rp 30,000 (45,000 ÷ 1.5)
- **Gram**: Rp 30 (30,000 ÷ 1,000)

## Alternative Database Access Methods

If you don't have direct database access:

1. **phpMyAdmin**: Usually accessible at `http://localhost/phpmyadmin` or `http://localhost:8080/phpmyadmin`
2. **XAMPP Control Panel**: If using XAMPP, click "Admin" next to MySQL
3. **WAMP/MAMP**: Similar to XAMPP, use the control panel
4. **Command Line**: Open Command Prompt and try: `mysql -u root -p eadt_umkm`
5. **Database GUI Tools**: 
   - MySQL Workbench (free, official MySQL tool)
   - HeidiSQL (free, Windows)
   - DBeaver (free, cross-platform)
   - TablePlus (paid, but has free tier)

## Quick Access Guide

### For XAMPP Users:
1. Start XAMPP Control Panel
2. Make sure MySQL is running (green indicator)
3. Click "Admin" button next to MySQL
4. This opens phpMyAdmin in your browser
5. Select `eadt_umkm` database from left sidebar
6. Click "SQL" tab
7. Paste the fix script and click "Go"

### For Laravel Sail/Docker Users:
```bash
# Access MySQL container
docker exec -it <container_name> mysql -u root -p eadt_umkm

# Or use Laravel Sail
./vendor/bin/sail mysql
```

### For Direct MySQL Installation:
```bash
# Windows Command Prompt
mysql -u root -p eadt_umkm

# Mac/Linux Terminal
mysql -u root -p eadt_umkm
```

## Important Notes

- **Backup First**: Always backup your database before making changes
  ```sql
  -- Create backup (run in command line)
  mysqldump -u root -p eadt_umkm > backup_before_fix.sql
  ```
- **Run as Transaction**: If your tool supports it, wrap the fix in a transaction:
  ```sql
  START TRANSACTION;
  -- (run all the fix commands here)
  COMMIT;
  -- If something goes wrong: ROLLBACK;
  ```
- **Test After**: Check the stock report page to confirm it shows 28 units instead of 57
- **Monitor**: Watch for any other items with similar issues
- **Clear Cache**: After fixing, you may need to clear Laravel cache:
  ```bash
  php artisan cache:clear
  php artisan config:clear
  php artisan view:clear
  ```

## Troubleshooting

### Issue: "Access denied for user 'root'@'localhost'"
**Solution**: Check your `.env` file for correct database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eadt_umkm
DB_USERNAME=root
DB_PASSWORD=
```

### Issue: "Unknown database 'eadt_umkm'"
**Solution**: The database doesn't exist. Create it first:
```sql
CREATE DATABASE eadt_umkm;
```

### Issue: "Table 'stock_movements' doesn't exist"
**Solution**: Run Laravel migrations first:
```bash
php artisan migrate
```

### Issue: Still showing 57 units after fix
**Solution**: 
1. Clear browser cache (Ctrl+F5)
2. Clear Laravel cache (see commands above)
3. Verify the fix was applied by running the verification queries
4. Check if there are other stock_movements records being created

## Summary

The fix addresses two main issues:
1. **Duplicate stock layers** causing incorrect sum calculations in `getAvailableQty()` method
2. **Missing sub-unit configuration** for proper multi-unit display

After applying the fix:
- Stock will correctly show 28 Ekor (not 57)
- All 4 unit conversions will work properly
- Total value remains consistent at Rp 1,260,000 across all units