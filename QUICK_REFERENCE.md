# Quick Reference - Customer Website to Owner Dashboard Integration

## 🎯 What Was Fixed

### Problem
Data dari website pelanggan tidak masuk ke dashboard owner. Penjualan tidak muncul setelah checkout.

### Solution
Implemented complete order-to-penjualan synchronization with proper multi-owner support.

---

## 📊 How It Works Now

### Customer Checkout Flow
```
1. Customer adds items to cart
2. Customer clicks "Checkout"
3. System validates stock
4. Order created
5. Stock decremented
6. Penjualan created for each owner
7. Payment processed
8. Owner sees transaction in dashboard
```

### Key Points
- ✅ Transactions appear **immediately** in owner dashboard
- ✅ Multi-owner orders handled correctly (separate Penjualan per owner)
- ✅ Stock display consistent across all pages
- ✅ Payment status tracked properly
- ✅ Journals created automatically when payment confirmed

---

## 🔧 Important Implementation Details

### For Developers

#### 1. Always Use `withoutGlobalScopes()` for Cross-Owner Data
```php
// ✅ CORRECT - When accessing products from other owners
$produk = Produk::withoutGlobalScopes()->find($id);

// ❌ WRONG - Will fail for products from other owners
$produk = Produk::find($id);
```

#### 2. Use `is_null()` Not `empty()`
```php
// ✅ CORRECT - Allows 0 as valid value
if (is_null($penjualan->user_id)) {
    $penjualan->user_id = auth()->id();
}

// ❌ WRONG - Treats 0 as falsy
if (empty($penjualan->user_id)) {
    $penjualan->user_id = auth()->id();
}
```

#### 3. Use `now()->toDateString()` Not `now()->date()`
```php
// ✅ CORRECT
$tanggal = now()->toDateString();

// ❌ WRONG - date() method doesn't exist on Carbon
$tanggal = now()->date();
```

#### 4. Sync Must Be Called AFTER OrderItems Creation
```php
// ✅ CORRECT - In CheckoutController after OrderItems created
OrderItem::create([...]);
$service = new OrderToSalesService();
$service->syncOrderToPenjualan($order);

// ❌ WRONG - In OrderObserver::created() before OrderItems exist
// This will fail because items are not created yet
```

#### 5. Always Filter by `user_id` for Multi-Tenant
```php
// ✅ CORRECT - Multi-tenant isolation
$penjualans = Penjualan::where('user_id', auth()->id())->get();

// ❌ WRONG - Shows all penjualans from all owners
$penjualans = Penjualan::all();
```

---

## 📁 Key Files to Know

### Controllers
- `app/Http/Controllers/Pelanggan/CheckoutController.php` - Main checkout flow
- `app/Http/Controllers/Pelanggan/CartController.php` - Cart management
- `app/Http/Controllers/Pelanggan/DashboardController.php` - Product listing

### Models
- `app/Models/Order.php` - Customer orders
- `app/Models/Penjualan.php` - Owner sales (synced from orders)
- `app/Models/Produk.php` - Products (multi-owner)

### Services
- `app/Services/OrderToSalesService.php` - Order to Penjualan conversion

### Observers
- `app/Observers/OrderObserver.php` - Order event handling

### Middleware
- `app/Http/Middleware/Authenticate.php` - Authentication with guard support

---

## 🧪 Testing Checklist

### Before Pushing
- [ ] All PHP files have correct syntax
- [ ] All migrations are in place
- [ ] No errors in diagnostics

### After Deployment
- [ ] Customer can add items to cart
- [ ] Customer can checkout successfully
- [ ] Stock decrements correctly
- [ ] Penjualan appears in owner dashboard immediately
- [ ] Payment status is correct (paid for kasir, pending for COD)
- [ ] Stock display is consistent (dashboard = menu)
- [ ] Multi-owner orders create separate Penjualan per owner

---

## 🐛 Troubleshooting

### Issue: "Terjadi kesalahan saat menambahkan ke keranjang"
**Solution**: Check if Authenticate middleware is loaded in Kernel.php

### Issue: Penjualan tidak muncul setelah checkout
**Solution**: 
1. Check logs: `storage/logs/laravel.log`
2. Verify OrderToSalesService was called
3. Check Penjualan table for records
4. Verify user_id matches owner ID

### Issue: Stock tidak berkurang
**Solution**: 
1. Check if stock decrement query executed
2. Verify Produk::withoutGlobalScopes() is used
3. Check StockMovement table for records

### Issue: Dashboard stok berbeda dengan menu produk
**Solution**: 
1. DashboardController should use `stok` column directly
2. Not calculated from stock_movements
3. Both should show same value

---

## 📞 Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review INTEGRATION_STATUS_REPORT.md
3. Check DEPLOYMENT_GUIDE.md for detailed setup

---

## 🚀 Deployment Command

```bash
# Pull latest changes
git pull origin main

# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear

# Test checkout flow
# Verify transactions appear in owner dashboard
```

---

Last Updated: May 17, 2026
Status: ✅ READY FOR PRODUCTION
