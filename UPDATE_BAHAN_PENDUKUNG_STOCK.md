# Update Bahan Pendukung Stock from 50 to 200

Since there's a syntax error preventing PHP execution, please run these SQL commands directly in your database to update the bahan pendukung stock:

## Option 1: Using phpMyAdmin or MySQL Workbench

1. Open your database management tool (phpMyAdmin, MySQL Workbench, etc.)
2. Select your Laravel database
3. Run this SQL command:

```sql
UPDATE bahan_pendukungs SET stok = 200 WHERE stok = 50;
```

## Option 2: Using MySQL Command Line

1. Open command prompt/terminal
2. Connect to MySQL:
```bash
mysql -u your_username -p your_database_name
```
3. Run the update command:
```sql
UPDATE bahan_pendukungs SET stok = 200 WHERE stok = 50;
```

## Option 3: Check Current Stock First

To see current stock levels before updating:
```sql
SELECT id, nama_bahan, stok FROM bahan_pendukungs ORDER BY id;
```

Then update:
```sql
UPDATE bahan_pendukungs SET stok = 200 WHERE stok = 50;
```

Verify the update:
```sql
SELECT id, nama_bahan, stok FROM bahan_pendukungs ORDER BY id;
```

## What This Does

This SQL command will:
- Find all bahan pendukung records that currently have stock = 50
- Update their stock to 200
- This affects the actual database records that show in your application

After running this SQL command, refresh your browser and the stock should show 200 instead of 50.

## Alternative: Manual Update

If you prefer to update specific items manually:

```sql
-- Update specific bahan pendukung by name
UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Air';
UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Minyak Goreng';
UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Gas';
UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Kemasan';
```

## Note

The application logic has already been updated to use 200 as the base stock for validation. This SQL update will make the database records match the application logic.