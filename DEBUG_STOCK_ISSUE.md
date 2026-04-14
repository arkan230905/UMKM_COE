# Debug Stock Update Issue

## 🎯 PROBLEM
- Stock report shows correct values (stock movements working)
- Master table `bahan_bakus.stok` field not updating during purchases
- Need to identify where the stock update logic is failing

## 🔍 DEBUGGING STEPS

### Step 1: Test Helper Function Directly
Visit this URL to test if the helper function works:
```
/debug/test-stock-helper/{bahanId}/{qty}/{type}
```

**Example:**
```
/debug/test-stock-helper/1/5.5/in
```

**Expected Result:**
```json
{
  "success": true,
  "material_type": "BahanBaku",
  "material_name": "Tepung Terigu",
  "stock_before": 10.0,
  "stock_after": 15.5,
  "qty_applied": "5.5",
  "type": "in",
  "difference": 5.5
}
```

**If this FAILS:** The helper function has issues
**If this WORKS:** The issue is in the purchase controller logic

### Step 2: Check Purchase Stock Update
After making a purchase, visit:
```
/debug/stock-after-purchase/{pembelianId}
```

This will show:
- Purchase details
- Expected quantities
- Current stock values
- Whether stock was updated

### Step 3: Check Laravel Logs
Look for these log entries in `storage/logs/laravel.log`:

**Before Stock Update:**
```
BEFORE STOCK UPDATE - Bahan Baku ID X:
```

**Stock Update Process:**
```
STOCK UPDATE - Bahan Baku ID X:
```

**After Stock Update:**
```
AFTER STOCK UPDATE - Bahan Baku ID X:
```

### Step 4: Manual Database Check
Run this query to check if stock is actually updating:
```sql
SELECT id, nama_bahan, stok, updated_at 
FROM bahan_bakus 
WHERE id IN (1,2,3,4,5) 
ORDER BY updated_at DESC;
```

## 🔧 POSSIBLE ISSUES & FIXES

### Issue 1: Helper Function Not Called
**Symptoms:** No "BEFORE STOCK UPDATE" logs
**Fix:** Check if the purchase controller logic reaches the stock update section

### Issue 2: Conversion Logic Wrong
**Symptoms:** Stock update called but with wrong quantity
**Check:** Look for `qty_in_base_unit` values in logs
**Fix:** Verify conversion calculation

### Issue 3: Database Transaction Rollback
**Symptoms:** Stock updates in logs but not in database
**Fix:** Check for exceptions after stock update that cause rollback

### Issue 4: Model Save Issues
**Symptoms:** Helper function returns false
**Fix:** Check model validation rules or database constraints

### Issue 5: Field Not Exists
**Symptoms:** Database errors about `stok` field
**Fix:** Verify `stok` field exists in both `bahan_bakus` and `bahan_pendukungs` tables

## 🧪 TESTING PROCEDURE

1. **Test Helper Function:**
   ```
   GET /debug/test-stock-helper/1/10/in
   ```

2. **Make a Test Purchase:**
   - Create a small purchase with 1 item
   - Note the purchase ID

3. **Check Purchase Results:**
   ```
   GET /debug/stock-after-purchase/{purchaseId}
   ```

4. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep "STOCK UPDATE"
   ```

5. **Verify Database:**
   ```sql
   SELECT * FROM bahan_bakus WHERE id = {testedId};
   ```

## 🎯 EXPECTED BEHAVIOR

**When Purchase is Made:**
1. ✅ Stock movement recorded (for reports)
2. ✅ `bahan_bakus.stok` field updated (for master data)
3. ✅ Both values should match

**Debug Output Should Show:**
- Helper function test: `success: true`
- Purchase debug: Stock values increased
- Logs: All stock update steps completed
- Database: `stok` field updated with correct values

## 🚨 CRITICAL CHECKS

1. **Conversion Factor:** Ensure `qty_in_base_unit` is calculated correctly
2. **Transaction Scope:** Stock update must be inside the DB transaction
3. **Error Handling:** No exceptions should occur after stock update
4. **Field Names:** Verify `stok` field exists and is writable
5. **Model Relations:** Ensure BahanBaku/BahanPendukung models are loaded correctly

## 📝 DEBUGGING COMMANDS

```bash
# Check recent purchases
php artisan tinker --execute="App\Models\Pembelian::with('details')->latest()->first()"

# Check stock values
php artisan tinker --execute="App\Models\BahanBaku::select('id','nama_bahan','stok')->limit(5)->get()"

# Test helper function
php artisan tinker --execute="App\Models\BahanBaku::first()->updateStok(1, 'in', 'test')"
```

Follow these steps systematically to identify exactly where the stock update is failing!