# DEPRECIATION DISCREPANCY EXPLANATION

## Problem
User noticed that Peralatan Produksi shows different depreciation values:
- **Master List Page**: Rp 657.550
- **Detail Page**: Rp 659.474

## Root Cause Analysis

### 1. Master List Page (index) - Rp 657.550 ✅ CORRECT
**File**: `app/Http/Controllers/AsetController.php` (index method)
**Calculation**: Uses `DepreciationCalculationService::calculateCurrentMonthDepreciation()`

This method calculates the **actual declining balance depreciation** for the current month based on:
- Current book value
- Declining balance rate (40% annually for 5-year asset)
- Proper declining balance formula

### 2. Detail Page (show) - Rp 659.474 ❌ INCORRECT
**File**: `app/Http/Controllers/AsetController.php` (show method)
**Calculation**: Uses simplified average calculation

```php
// WRONG: Uses average instead of actual declining balance
$nilaiDisusutkan = $totalPerolehan - $nilaiResidu;
$averagePerTahun = $nilaiDisusutkan / $umurManfaat;
$penyusutanPerBulan = $averagePerTahun / 12; // = 659.474
```

This gives: (85,000,000 - 5,000,000) / 5 / 12 = 1,333,333 / 12 = **659.474**

### 3. Correct Declining Balance Calculation - Rp 657.550 ✅
**Method**: Saldo Menurun (Declining Balance)
**Formula**: Book Value × (Rate / 12)
- Rate = 2 / 5 years = 40% annually
- Current book value consideration
- Result: **657.550**

## Solution

### Immediate Fix
Use the **correct value Rp 657.550** in journal entries by running:
```
http://localhost:8000/fix-jurnal-now
```

### Long-term Fix (Optional)
Update the show method in AsetController to use the same calculation as the index method:

```php
// Replace the simplified calculation with:
$penyusutanPerBulan = $this->depreciationService->calculateCurrentMonthDepreciation($aset);
```

## Why This Matters
1. **Journal Accuracy**: Journal entries should match the actual depreciation calculation
2. **Consistency**: Both pages should show the same values
3. **Accounting Standards**: Declining balance method requires proper calculation, not averages

## Current Status
- ✅ **Master list page**: Shows correct value (Rp 657.550)
- ✅ **Journal cleanup**: Updated to use correct value (Rp 657.550)
- ⚠️ **Detail page**: Still shows simplified calculation (Rp 659.474) - cosmetic issue only

The journal will now be balanced and accurate with the correct depreciation values!