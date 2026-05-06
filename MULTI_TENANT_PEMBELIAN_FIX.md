# Multi-Tenant Pembelian System Fix

## Overview
Fixed multi-tenant isolation issues in the pembelian (purchase) system to ensure all data is properly filtered by `user_id`.

## Date: May 6, 2026

## Issues Fixed

### 1. Missing `purchase_return_items` Table
**Problem:** Table didn't exist, causing error when accessing pembelian page
**Solution:** 
- Created migration `2026_05_06_051938_create_purchase_return_items_table.php`
- Added `user_id` column with foreign key constraint
- Created `PurchaseReturnItem` model with auto-fill `user_id` in boot method

### 2. Missing `user_id` in `stock_movements` Table
**Problem:** Stock movements weren't isolated by user, causing cross-tenant data leakage
**Solution:**
- Created migration `2026_05_06_052224_add_user_id_to_stock_movements_table.php`
- Added `user_id` column with foreign key constraint
- Updated existing 11 records with `user_id` from related tables (pembelians, penjualans, produksis)
- Added composite index: `['user_id', 'item_type', 'item_id']`
- Updated `StockMovement` model to auto-fill `user_id` in boot method

### 3. Missing `user_id` in `stock_layers` Table
**Problem:** Stock layers weren't isolated by user
**Solution:**
- Created migration `2026_05_06_052243_add_user_id_to_stock_layers_table.php`
- Added `user_id` column with foreign key constraint
- Updated existing records (0 records found)
- Added composite index: `['user_id', 'item_type', 'item_id']`
- Updated `StockLayer` model to auto-fill `user_id` in boot method

### 4. Missing `user_id` in `pembelian_detail_konversi` Table
**Problem:** Manual conversion data wasn't isolated by user
**Solution:**
- Created migration `2026_05_06_052301_add_user_id_to_pembelian_detail_konversi_table.php`
- Added `user_id` column with foreign key constraint
- Updated existing records (0 records found) via JOIN with pembelian_details and pembelians
- Added composite index: `['user_id', 'pembelian_detail_id']`
- Updated `PembelianDetailKonversi` model to auto-fill `user_id` in boot method

## Files Modified

### Models Updated
1. **app/Models/PurchaseReturnItem.php**
   - Added `user_id` to fillable
   - Added boot method to auto-fill `user_id`

2. **app/Models/StockMovement.php**
   - Added `user_id` to fillable
   - Added boot method to auto-fill `user_id`
   - Kept existing booted method for delete events

3. **app/Models/StockLayer.php**
   - Added `user_id` to fillable
   - Added boot method to auto-fill `user_id`

4. **app/Models/PembelianDetailKonversi.php**
   - Added `user_id` to fillable
   - Added boot method to auto-fill `user_id`

### Migrations Created
1. `database/migrations/2026_05_06_051938_create_purchase_return_items_table.php`
2. `database/migrations/2026_05_06_052224_add_user_id_to_stock_movements_table.php`
3. `database/migrations/2026_05_06_052243_add_user_id_to_stock_layers_table.php`
4. `database/migrations/2026_05_06_052301_add_user_id_to_pembelian_detail_konversi_table.php`

## Controllers Already Fixed (from previous context)
1. **app/Http/Controllers/PembelianController.php**
   - `index()`: Filters pembelians and vendors by `user_id`
   - `show()`: Filters pembelian by `user_id`
   - `create()`: Filters vendors, bahan_bakus, bahan_pendukungs by `user_id`
   - `edit()`: Filters by `user_id` (from context)
   - `update()`: Filters by `user_id` (from context)
   - `destroy()`: Filters by `user_id` (from context)

2. **app/Http/Controllers/ReturController.php**
   - `getRetursData()`: Filters purchase_returns by `user_id`
   - `getRetursDataForPembelian()`: Public method for PembelianController
   - All retur operations filter by `user_id`

## Database Schema Changes

### purchase_return_items
```sql
CREATE TABLE purchase_return_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    purchase_return_id BIGINT UNSIGNED NOT NULL,
    pembelian_detail_id BIGINT UNSIGNED NULL,
    bahan_baku_id BIGINT UNSIGNED NULL,
    bahan_pendukung_id BIGINT UNSIGNED NULL,
    unit VARCHAR(50) NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (purchase_return_id) REFERENCES purchase_returns(id) ON DELETE CASCADE,
    INDEX idx_user_purchase_return (user_id, purchase_return_id)
);
```

### stock_movements
```sql
ALTER TABLE stock_movements 
ADD COLUMN user_id BIGINT UNSIGNED NOT NULL AFTER id,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
ADD INDEX idx_user_item (user_id, item_type, item_id);
```

### stock_layers
```sql
ALTER TABLE stock_layers 
ADD COLUMN user_id BIGINT UNSIGNED NOT NULL AFTER id,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
ADD INDEX idx_user_item (user_id, item_type, item_id);
```

### pembelian_detail_konversi
```sql
ALTER TABLE pembelian_detail_konversi 
ADD COLUMN user_id BIGINT UNSIGNED NOT NULL AFTER id,
ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
ADD INDEX idx_user_detail (user_id, pembelian_detail_id);
```

## Data Migration Results
- **stock_movements**: 11 records updated with `user_id`
- **stock_layers**: 0 records (table was empty)
- **pembelian_detail_konversi**: 0 records (table was empty)
- **purchase_return_items**: New table created

## Testing Checklist
- [x] Pembelian index page loads without errors
- [ ] Pembelian create page works correctly
- [ ] Pembelian edit page works correctly
- [ ] Pembelian delete works correctly
- [ ] Pembelian detail page shows correct data
- [ ] Retur pembelian tab shows correct data
- [ ] Stock movements are filtered by user_id
- [ ] Stock layers are filtered by user_id
- [ ] Manual conversion data is filtered by user_id

## Next Steps
1. Test pembelian CRUD operations
2. Test retur pembelian functionality
3. Verify stock movements are properly isolated
4. Verify no cross-tenant data leakage

## Notes
- All new records will automatically have `user_id` filled via model boot methods
- All existing records have been updated with correct `user_id`
- Foreign key constraints ensure data integrity
- Composite indexes improve query performance for multi-tenant queries
- All queries in controllers already filter by `user_id` (from previous fixes)
