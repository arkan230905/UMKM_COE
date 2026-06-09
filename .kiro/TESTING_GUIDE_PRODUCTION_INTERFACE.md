# Testing Guide - Production Interface Redesign

## Date: June 8, 2026

---

## Quick Start Testing

### 1. Navigate to Production Page
```
URL: http://your-domain.com/transaksi/produksi
```

You should see two tabs:
- **Data Produk** (active by default)
- **Riwayat Produksi**

---

## Test Case 1: View Data Produk Tab

### Expected Result:
✅ Shows products that have been setup for production
✅ Each product displays:
   - Product name
   - Last created date/time
   - Production specifications (monthly qty, working days, qty per day)
   - Total cost per day
   - Stock status badge (Cukup/Kurang)
   - Action buttons (View Detail, Mulai Produksi)

### What to Check:
- [ ] Product list loads correctly
- [ ] Stock status shows correctly (Green = Cukup, Red = Kurang)
- [ ] "Mulai Produksi" button is enabled for products with sufficient stock
- [ ] "Stok Kurang" button is disabled for products with insufficient stock
- [ ] Hover over "Kurang" badge shows shortage details
- [ ] Badge counters show correct count

---

## Test Case 2: Start Production from Template

### Steps:
1. In "Data Produk" tab, find a product with "Cukup" (sufficient) stock status
2. Click the "Mulai Produksi" button
3. Confirm the action in the alert dialog

### Expected Result:
✅ Page redirects to "Riwayat Produksi" tab
✅ Success message appears: "Produksi baru untuk [Product Name] berhasil dibuat. Qty: [X]. Klik 'Mulai' untuk memulai produksi."
✅ New production record appears at the top of the list
✅ Status shows as "Siap Produksi" (blue badge)
✅ "Mulai" button is available

### What to Check:
- [ ] Redirect happens automatically
- [ ] Success message displays correctly
- [ ] New production record exists with correct data:
  - Product name
  - Today's date
  - Correct qty
  - Status = "draft"
- [ ] Production details are copied from template
- [ ] Production processes are copied from template

---

## Test Case 3: View Riwayat Produksi Tab

### Expected Result:
✅ Shows all production records (draft, dalam_proses, selesai)
✅ Each record displays:
   - Date created
   - Product name
   - Qty
   - Total cost
   - Status badge
   - Action buttons based on status

### Status Types:
- **Siap Produksi** (Blue) - Draft status, "Mulai" button available
- **Dalam Proses** (Primary) - In progress, "Kelola" button available
- **Selesai** (Green) - Completed, "Detail" button only

### What to Check:
- [ ] All production records load correctly
- [ ] Pagination works if there are many records
- [ ] Status badges show correct colors
- [ ] Action buttons match the status

---

## Test Case 4: Use Filters

### Steps:
1. In "Riwayat Produksi" tab, use the filter form
2. Try filtering by:
   - Date range (tanggal_mulai & tanggal_selesai)
   - Product (produk_id)
   - Status (draft, dalam_proses, selesai)
3. Click "Filter" button

### Expected Result:
✅ Results filtered correctly
✅ Filter values retained in form
✅ URL parameters show active filters
✅ "(Filter Aktif)" text appears in header

### What to Check:
- [ ] Date filter works
- [ ] Product filter works
- [ ] Status filter works
- [ ] Combined filters work
- [ ] "Reset" button clears all filters

---

## Test Case 5: Start Production (Reduce Stock)

### Steps:
1. In "Riwayat Produksi" tab, find a production with status "Siap Produksi"
2. Click the "Mulai" button
3. Confirm the action

### Expected Result:
✅ Redirects to production process page
✅ Success message: "Produksi berhasil dimulai. Stok bahan baku telah dikurangi..."
✅ Stock of bahan baku reduced in database
✅ Stock of bahan pendukung reduced in `saldo_awal`
✅ Stock movements recorded for all materials
✅ Production status changed to "dalam_proses"
✅ Product finished goods stock increased

### What to Check:
- [ ] Stock reduced correctly for all bahan baku
- [ ] Stock reduced correctly for all bahan pendukung
- [ ] Stock movements table has new entries
- [ ] Production status = "dalam_proses"
- [ ] Finished goods stock increased by qty_produksi

### Verify in Database:
```sql
-- Check bahan baku stock
SELECT id, nama_bahan, stok FROM bahan_bakus WHERE id IN ([material IDs]);

-- Check bahan pendukung stock
SELECT id, nama_bahan, saldo_awal FROM bahan_pendukungs WHERE id IN ([support IDs]);

-- Check stock movements
SELECT * FROM stock_movements WHERE ref_type='produksi' AND ref_id=[production_id];

-- Check production status
SELECT id, produk_id, status, tanggal_mulai FROM produksis WHERE id=[production_id];
```

---

## Test Case 6: Tab Navigation

### Test URL Parameters:
1. Navigate to: `/transaksi/produksi`
   - Expected: "Data Produk" tab active
   
2. Navigate to: `/transaksi/produksi?tab=riwayat`
   - Expected: "Riwayat Produksi" tab active

### What to Check:
- [ ] Default tab is "Data Produk"
- [ ] URL parameter switches to correct tab
- [ ] Tab switching works by clicking
- [ ] Active tab highlighted correctly

---

## Test Case 7: Stock Validation

### Test Insufficient Stock:
1. In database, reduce stock of a material used in production
2. Refresh "Data Produk" tab
3. Check product that uses that material

### Expected Result:
✅ Stock status badge shows "Kurang" (red)
✅ "Mulai Produksi" button is disabled and shows "Stok Kurang"
✅ Hover tooltip shows which materials are short and by how much

### What to Check:
- [ ] Badge color changes to red
- [ ] Button becomes disabled
- [ ] Button text changes to "Stok Kurang"
- [ ] Tooltip shows shortage details
- [ ] Multiple shortages are listed

---

## Test Case 8: Multi-Tenant Isolation

### Security Test:
1. Login as User A
2. Create production for Product A
3. Logout and login as User B
4. Navigate to production page

### Expected Result:
✅ User B does NOT see User A's products in "Data Produk"
✅ User B does NOT see User A's productions in "Riwayat Produksi"
✅ Attempting to access User A's production URL returns 404

### What to Check:
- [ ] Data isolation by user_id
- [ ] No cross-tenant data leakage
- [ ] URL manipulation returns 404

---

## Test Case 9: Template Data Integrity

### Steps:
1. Create production from template
2. Check database to verify all data copied

### Expected Result:
✅ Production record created with correct data
✅ All production_details copied (bahan baku + bahan pendukung)
✅ All production_proses copied (BTKL + BOP)
✅ All values match template exactly
✅ Status set to 'draft'
✅ New IDs assigned (not referencing old IDs)

### Verify in Database:
```sql
-- Original template
SELECT * FROM produksis WHERE id=[template_id];
SELECT * FROM produksi_details WHERE produksi_id=[template_id];
SELECT * FROM produksi_proses WHERE produksi_id=[template_id];

-- New production
SELECT * FROM produksis WHERE id=[new_production_id];
SELECT * FROM produksi_details WHERE produksi_id=[new_production_id];
SELECT * FROM produksi_proses WHERE produksi_id=[new_production_id];
```

---

## Test Case 10: BOP Structure Integration

### What to Check:
- [ ] Bahan pendukung from BOP komponen_bahan_pendukung appears in production details
- [ ] Bahan pendukung stock reduces from `saldo_awal` column
- [ ] Stock movements use item_type='bahan_pendukung'
- [ ] COA from BOP configuration used in journal entries (when production completes)

### Verify BOP Data:
```sql
-- Check BOP structure
SELECT id, komponen_bahan_pendukung, komponen_lainnya, total_bop_per_produk 
FROM bop_proses WHERE id=[bop_id];

-- Verify JSON structure
SELECT 
  JSON_EXTRACT(komponen_bahan_pendukung, '$[0].bahan_pendukung_id') as bp_id,
  JSON_EXTRACT(komponen_bahan_pendukung, '$[0].coa_debit') as coa_debit
FROM bop_proses WHERE id=[bop_id];
```

---

## Expected Performance

### Page Load Time:
- Initial load: < 2 seconds
- Tab switching: < 200ms (client-side)
- Filter application: < 1 second

### Database Queries:
- Data Produk tab: ~3-4 queries (grouped by produk_id)
- Riwayat tab: ~2-3 queries (with pagination)
- Production creation: ~5-10 queries (within transaction)

---

## Common Issues & Solutions

### Issue 1: "Route not found" error
**Solution:** Run `php artisan route:clear`

### Issue 2: Old view still showing
**Solution:** Run `php artisan view:clear`

### Issue 3: Stock not reducing
**Solution:** Check if production status changed to "dalam_proses" - stock only reduces when clicking "Mulai" button

### Issue 4: Blank tab content
**Solution:** Check browser console for JavaScript errors. Clear cache.

### Issue 5: Stock status always shows "Kurang"
**Solution:** Verify stock in database. Check unit conversions for bahan baku.

---

## Debugging Tools

### Laravel Logs:
```bash
tail -f storage/logs/laravel.log
```

### Query Log (in Controller):
```php
\DB::enableQueryLog();
// ... your code
dd(\DB::getQueryLog());
```

### Vue DevTools:
Check component data and props

### Browser DevTools:
- Network tab: Check AJAX requests
- Console: Check JavaScript errors

---

## Success Criteria

All test cases pass:
- ✅ Data Produk tab loads correctly
- ✅ Stock validation works
- ✅ Production creation from template works
- ✅ Riwayat tab shows all productions
- ✅ Filters work correctly
- ✅ Stock reduces when starting production
- ✅ Multi-tenant isolation maintained
- ✅ Template data copied correctly
- ✅ BOP integration works

**If all tests pass, the feature is ready for production!**

---

## Next Steps After Testing

1. Monitor production usage for 1 week
2. Gather user feedback
3. Optimize queries if needed
4. Add any requested enhancements
5. Document any issues found

---

## Contact & Support

If issues are found during testing:
1. Check `.kiro/PRODUCTION_INTERFACE_REDESIGN.md` for implementation details
2. Check `.kiro/PRODUCTION_BOP_FIX_SUMMARY.md` for BOP structure
3. Review ProduksiController methods
4. Check database data integrity

**Status: Ready for Testing ✅**
