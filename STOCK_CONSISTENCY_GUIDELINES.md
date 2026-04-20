# Stock Data Consistency Guidelines

## Overview
This document outlines the guidelines for maintaining consistency in stock data across the application to prevent future issues.

## Stock Data Sources

### Primary Stock Source: `produks.stok` Column
- **ALWAYS use** the `stok` column from the `produks` table as the primary source of stock data
- This is the master stock data that should be displayed in all user interfaces
- All stock validations should be based on this column

### Secondary Stock Source: `stock_layers.remaining_qty` (StockLayer)
- Used internally for FIFO cost calculations and stock movement tracking
- Should NOT be used for user-facing stock displays
- Only used by StockService for cost accounting purposes

## Implementation Rules

### 1. Penjualan (Sales) Module
- **Display Stock**: Use `$produk->stok` in all dropdowns and forms
- **Validation**: Validate against `$produk->stok` before allowing sales
- **API Endpoints**: Return `$produk->stok` in all JSON responses
- **JavaScript**: Use `stok` field in all client-side calculations

### 2. Stock Display Consistency
```php
// ✅ CORRECT - Use stok from produks table
$stokTersedia = (float)($produk->stok ?? 0);

// ❌ WRONG - Don't use actual_stok from StockLayer
$stokTersedia = (float)$produk->actual_stok;
```

### 3. Controller Methods
All controller methods should use consistent stock references:
- `create()` - Use `$p->stok` for product listings
- `store()` - Validate against `$p->stok`
- `edit()` - Use `$p->stok` for product listings
- `update()` - Validate against `$p->stok`
- `findByBarcode()` - Return `$p->stok` in API response
- `searchProducts()` - Return `$p->stok` in API response

### 4. View Templates
All Blade templates should display stock from the same source:
```blade
{{-- ✅ CORRECT --}}
<option value="{{ $p->id }}" data-stok="{{ $p->stok }}">
    {{ $p->nama_produk }} (Stok: {{ number_format($p->stok, 0, ',', '.') }})
</option>

{{-- ❌ WRONG --}}
<option value="{{ $p->id }}" data-stok="{{ $p->actual_stok }}">
```

### 5. JavaScript Consistency
```javascript
// ✅ CORRECT - Use stok field
productData[productId] = {
    id: productId,
    nama: productName,
    harga: productPrice,
    stok: productStock  // Use 'stok' field
};

// ❌ WRONG - Don't use actual_stok
productData[productId] = {
    actual_stok: productStock  // Don't use this
};
```

## Prevention Measures

### 1. Code Review Checklist
Before merging any changes related to stock functionality:
- [ ] Verify all stock references use `$produk->stok`
- [ ] Check that no `actual_stok` is used in user-facing code
- [ ] Ensure API endpoints return consistent stock data
- [ ] Validate JavaScript uses `stok` field consistently

### 2. Testing Guidelines
- Test stock display consistency across all pages
- Verify API endpoints return correct stock values
- Check that stock validations work properly
- Ensure JavaScript calculations use correct stock data

### 3. Documentation Updates
- Update this document when adding new stock-related features
- Document any exceptions to these rules with clear justification
- Keep track of all files that handle stock data

## Files That Handle Stock Data

### Controllers
- `app/Http/Controllers/PenjualanController.php` - Sales transactions
- `app/Http/Controllers/MasterData/ProdukController.php` - Product management

### Views
- `resources/views/transaksi/penjualan/create.blade.php` - Sales form
- `resources/views/transaksi/penjualan/edit.blade.php` - Sales edit form
- `resources/views/master-data/produk/index.blade.php` - Product listing

### Models
- `app/Models/Produk.php` - Product model with stock relationships

### Services
- `app/Services/StockService.php` - Internal stock movement tracking (uses StockLayer)

## Emergency Procedures

If stock inconsistency is discovered:

1. **Immediate Fix**: Update the problematic code to use `$produk->stok`
2. **Data Verification**: Check if data corruption occurred
3. **User Communication**: Inform users if manual data correction is needed
4. **Prevention Update**: Update this document with lessons learned

## Contact
For questions about stock data consistency, refer to this document or consult with the development team.

---
Last Updated: April 20, 2026