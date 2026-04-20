# Depreciation Methods - Corrected Implementation

## Overview
This document explains the corrected implementation of the three depreciation methods used in the asset management system.

## Key Principles

### 1. Depreciation Start Date Rule
- If acquisition date is after the 15th of the month → Start depreciation next month
- If acquisition date is on or before the 15th → Start depreciation same month

### 2. Monthly vs Annual Calculations
Each method has different characteristics:
- **Straight Line**: Constant monthly amount
- **Double Declining Balance**: Decreasing monthly amount based on current book value
- **Sum of Years Digits**: Decreasing yearly amount, but constant within each year

## Method Implementations

### 1. Straight Line Method (Garis Lurus)
**Formula**: `(Cost - Residual Value) ÷ (Useful Life in Years × 12)`

**Characteristics**:
- Constant monthly depreciation throughout the asset's life
- Simple and predictable

**Example** (Cost: 1,000,000, Residual: 100,000, Life: 5 years):
- Depreciable Amount: 900,000
- Monthly Depreciation: 900,000 ÷ 60 = 15,000
- Every month: 15,000 (constant)

### 2. Double Declining Balance Method (Saldo Menurun)
**Formula**: `Current Book Value × (2 ÷ Useful Life) ÷ 12`

**Characteristics**:
- Higher depreciation in early years, lower in later years
- Monthly amount decreases as book value decreases
- Cannot depreciate below residual value

**Example** (Cost: 1,000,000, Residual: 100,000, Life: 5 years):
- Annual Rate: 2 ÷ 5 = 40%
- Monthly Rate: 40% ÷ 12 = 3.33%
- Month 1: 1,000,000 × 3.33% = 33,333
- Month 2: 966,667 × 3.33% = 32,222
- Month 3: 934,445 × 3.33% = 31,148
- And so on... (decreasing each month)

### 3. Sum of Years Digits Method (Angka Tahun)
**Formula**: `(Depreciable Amount × Remaining Life) ÷ Sum of Years ÷ 12`

**Characteristics**:
- Decreasing yearly depreciation amounts
- Constant monthly amount within each year
- Front-loaded depreciation (more in early years)

**Example** (Cost: 1,000,000, Residual: 100,000, Life: 5 years):
- Sum of Years: 5+4+3+2+1 = 15
- Depreciable Amount: 900,000

**Year-by-year breakdown**:
- Year 1: (900,000 × 5) ÷ 15 = 300,000 annually = 25,000 monthly
- Year 2: (900,000 × 4) ÷ 15 = 240,000 annually = 20,000 monthly
- Year 3: (900,000 × 3) ÷ 15 = 180,000 annually = 15,000 monthly
- Year 4: (900,000 × 2) ÷ 15 = 120,000 annually = 10,000 monthly
- Year 5: (900,000 × 1) ÷ 15 = 60,000 annually = 5,000 monthly

## Implementation Notes

### Current Month Calculation
The `hitungPenyusutanPerBulanSaatIni()` method calculates the depreciation amount for the current month based on:
1. Which month/year of depreciation we're currently in
2. The specific method's formula
3. Current book value (for Double Declining Balance)

### Accumulated Depreciation Calculation
The `hitungAkumulasiPenyusutanSaatIni()` method calculates total depreciation from start date to current month:
1. Determines months elapsed since depreciation started
2. Applies method-specific calculations for each period
3. Ensures total doesn't exceed depreciable amount

### Book Value Calculation
Current Book Value = Original Cost - Accumulated Depreciation

## Key Corrections Made

1. **Double Declining Balance**: Now correctly uses monthly rate and current book value
2. **Sum of Years Digits**: Now correctly calculates monthly amounts within each year
3. **Boundary Checks**: Added checks to prevent depreciation below residual value
4. **Period Validation**: Added checks to stop depreciation after useful life ends

## Testing Verification

To verify the calculations are correct:
1. Check that monthly amounts follow the expected pattern for each method
2. Verify that accumulated depreciation never exceeds depreciable amount
3. Confirm that book value never goes below residual value
4. Ensure depreciation stops after useful life period

## Usage in Controllers

Controllers should use:
- `$aset->hitungPenyusutanPerBulanSaatIni()` for current month depreciation
- `$aset->hitungAkumulasiPenyusutanSaatIni()` for total accumulated depreciation
- `$aset->getNilaiBukuRealTimeAttribute()` for current book value

These methods now correctly implement the mathematical formulas for each depreciation method.