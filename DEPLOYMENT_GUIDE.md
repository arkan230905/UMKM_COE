# Deployment Guide - Customer Website to Owner Dashboard Integration

## Overview
This guide explains the changes made to integrate the customer website checkout flow with the owner dashboard penjualan system.

## What Was Changed

### 1. Core Integration Files (Modified)
- **app/Http/Controllers/Pelanggan/CheckoutController.php**
  - Added stock_movements creation after stock decrement
  - Added sync to Penjualan after OrderItems creation
  - Added payment status update for kasir method

- **app/Http/Controllers/Pelanggan/CartController.php**
  - Fixed null product error by loading with withoutGlobalScopes()

- **app/Http/Controllers/Pelanggan/DashboardController.php**
  - Changed stok_tersedia calculation to use stok column directly (not stock_movements)
  - Ensures consistency between dashboard and product menu

- **app/Observers/OrderObserver.php** (New)
  - Removed sync from created() method
  - Enhanced updated() method to sync payment_status

- **app/Services/OrderToSalesService.php** (New)
  - Handles Order to Penjualan conversion
  - Supports multi-owner orders
  - Loads products with withoutGlobalScopes()

- **app/Models/Penjualan.php**
  - Added order_id foreign key
  - Added payment_status tracking
  - Deferred journal creation until payment confirmed

- **app/Models/Order.php**
  - Added payment_status and snap_token columns

### 2. New Files Created
- **app/Http/Middleware/Authenticate.php** - Authentication middleware for pelanggan guard
- **app/Services/DistanceService.php** - Distance calculation for ongkir
- **app/Services/IndonesiaAddressService.php** - Address service
- **app/Services/RajaOngkirService.php** - Shipping cost calculation
- **app/Models/Province.php, City.php, District.php, SubDistrict.php** - Location models
- **app/Models/CustomerAddress.php** - Customer address model
- **app/Console/Commands/SeedIndonesianAddresses.php** - Seed Indonesian locations

### 3. Database Migrations (New)
- **2026_05_15_172418_add_store_location_to_users_table.php** - Store location for ongkir
- **2026_05_15_200140_create_provinces_table.php** - Provinces table
- **2026_05_15_200344_create_cities_table.php** - Cities table
- **2026_05_15_201046_create_districts_table.php** - Districts table
- **2026_05_15_201308_create_sub_districts_table.php** - Sub-districts table
- **2026_05_15_202700_create_customer_addresses_table.php** - Customer addresses
- **2026_05_16_163704_add_bank_info_to_perusahaan_table.php** - Bank info for owners
- **2026_05_16_add_kasir_cod_to_payment_method.php** - Add kasir and COD payment methods
- **2026_05_16_add_order_id_and_payment_status_to_penjualans.php** - Link orders to penjualans
- **2026_05_17_create_integration_tables.php** - Ensure all integration tables exist

## Deployment Steps

### 1. Pull the Code
```bash
git pull origin main
```

### 2. Run Migrations
```bash
php artisan migrate
```

This will:
- Create all necessary tables
- Add required columns to existing tables
- Set up foreign keys and indexes

### 3. Seed Indonesian Locations (Optional)
If you want to use the ongkir (shipping cost) feature:
```bash
php artisan db:seed --class=SeedIndonesianAddresses
```

### 4. Clear Cache
```bash
php artisan cache:clear
php artisan config:cache
```

## How It Works

### Customer Checkout Flow
1. Customer adds products to cart
2. Customer proceeds to checkout
3. Order is created with payment method (kasir, COD, transfer, QRIS, etc.)
4. OrderItems are created
5. Stock is decremented
6. Stock movement is recorded (for audit trail)
7. Penjualan is created and linked to Order
8. Payment status is updated
9. Owner sees transaction in dashboard

### Key Features
- **Multi-owner support**: Customers can buy from multiple sellers in one order
- **Stock tracking**: Stock movements are recorded for audit trail
- **Payment methods**: Supports kasir (pick up), COD, transfer, and QRIS
- **Real-time sync**: Penjualan appears immediately in owner dashboard
- **Consistent stock display**: Dashboard shows same stock as product menu

## Important Notes

### For Team Members
1. **Always run migrations** after pulling code
2. **Check database** for any errors during migration
3. **Test checkout flow** with different payment methods
4. **Verify penjualan** appears in owner dashboard

### Common Issues

**Issue**: Penjualan not appearing in dashboard
- **Solution**: Check that payment_status is 'paid' in orders table
- **Check**: Verify order_id is set in penjualans table

**Issue**: Stock not decreasing
- **Solution**: Ensure stock_movements table exists
- **Check**: Verify stock column in produks table is being decremented

**Issue**: Error during migration
- **Solution**: Check database logs for specific error
- **Check**: Ensure all previous migrations ran successfully

## Testing

### Test Checkout Flow
1. Login as customer
2. Add product to cart
3. Proceed to checkout
4. Select payment method (kasir recommended for testing)
5. Complete checkout
6. Verify:
   - Order created in orders table
   - OrderItems created
   - Stock decremented
   - Penjualan created in penjualans table
   - Penjualan linked to Order (order_id set)
   - Payment status is 'paid'

### Test Owner Dashboard
1. Login as owner
2. Go to Penjualan menu
3. Verify new penjualan appears
4. Check that payment_status is 'paid'
5. Verify order details match customer order

## Support

If you encounter any issues:
1. Check the logs: `storage/logs/laravel.log`
2. Verify database migrations: `php artisan migrate:status`
3. Check that all required tables exist
4. Ensure foreign keys are properly set up

## Files to Keep

These files are important for the integration:
- All files in `app/Http/Controllers/Pelanggan/`
- All files in `app/Services/`
- All files in `app/Models/`
- All files in `app/Observers/`
- All files in `app/Http/Middleware/`
- All migration files in `database/migrations/`

## Files Removed

These files were removed as they were only for debugging:
- Debug scripts (check_*.php, debug_*.php, etc.)
- Documentation files (CHANGES_SUMMARY.md, etc.)
- Test files (test_*.php, etc.)

## Next Steps

1. Deploy code to production
2. Run migrations
3. Test checkout flow
4. Monitor logs for any errors
5. Verify penjualan appears in owner dashboard
