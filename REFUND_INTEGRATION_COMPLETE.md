# Refund Integration - COMPLETED ✓

## Summary
Sistem pelunasan utang sekarang sudah **terintegrasi penuh** dengan sistem retur pembelian. Sisa utang otomatis berkurang ketika ada retur dengan jenis "refund" yang disetujui.

## What Was Fixed

### 1. Controller - PelunasanUtangController.php
**Problem**: Filter di method `create()` menggunakan perhitungan manual yang tidak memperhitungkan refund.

**Fixed**:
```php
// BEFORE
->filter(function($pembelian) {
    $sisaUtang = ($pembelian->total_harga ?? 0) - ($pembelian->terbayar ?? 0);
    return $sisaUtang > 0;
});

// AFTER
->filter(function($pembelian) {
    // Use accessor that considers refunds
    return $pembelian->sisa_utang > 0;
});
```

**Fixed**: Method `store()` update sisa_pembayaran
```php
// BEFORE
$pembelian->sisa_pembayaran = ($pembelian->total_harga ?? 0) - $pembelian->terbayar;

// AFTER
$pembelian->sisa_pembayaran = ($pembelian->total_harga ?? 0) - $pembelian->terbayar - $pembelian->total_refund;
```

### 2. View - create.blade.php
**Problem**: Dropdown menampilkan sisa utang dengan perhitungan manual tanpa refund.

**Fixed**:
```php
// BEFORE
@php
    $sisaUtang = ($pembayaran->total_harga ?? 0) - ($pembayaran->terbayar ?? 0);
@endphp

// AFTER
@php
    // Use accessor that considers refunds
    $sisaUtang = $pembayaran->sisa_utang;
@endphp
```

**Added**: Detail section now shows refund breakdown
- Total Pembelian
- Total Dibayar
- Total Refund (only shows if > 0)
- Sisa Utang (already reduced by refund)

### 3. Controller - ApSettlementController.php
**Fixed**: All methods (store, update, destroy) now calculate sisa_pembayaran with refund:
```php
$pembelian->sisa_pembayaran = max(0, ($pembelian->total_harga ?? 0) - $pembelian->terbayar - $pembelian->total_refund);
```

### 4. Database Update
**Action**: Updated all existing pembelian records to recalculate sisa_pembayaran with refund.

**Results**:
- Pembelian #7 (PB-20260513-0001): Sisa berkurang dari Rp 570.000 → Rp 495.000 (refund Rp 75.000)
- Pembelian #8 (PB-20260513-0002): Sisa berkurang dari Rp 290.000 → Rp 266.000 (refund Rp 24.000)

## Formula

### Sisa Utang
```
Sisa Utang = Total Pembelian - Total Dibayar - Total Refund
```

### Total Refund
Only counts returns with:
- `jenis_retur = 'refund'` (not 'tukar_barang')
- `status IN ('disetujui', 'dikirim', 'selesai')` (not 'pending' or 'ditolak')

### Status Lunas
Pembelian becomes "lunas" when:
```
Total Dibayar + Total Refund >= Total Pembelian
```

## Verification Results

### Pembelian with Refund - WORKING ✓

**PB-20260513-0001**:
- Total: Rp 570.000
- Terbayar: Rp 0
- Refund: Rp 75.000 (RTR20260515001, status: selesai)
- **Sisa Utang: Rp 495.000** ✓
- Calculation: 570.000 - 0 - 75.000 = 495.000

**PB-20260513-0002**:
- Total: Rp 290.000
- Terbayar: Rp 0
- Refund: Rp 24.000 (RTR20260515002, status: selesai)
- **Sisa Utang: Rp 266.000** ✓
- Calculation: 290.000 - 0 - 24.000 = 266.000

### Pembelian without Refund - WORKING ✓

**PB-20260514-0003**:
- Total: Rp 375.000
- Terbayar: Rp 0
- Refund: Rp 0 (has retur but jenis = 'tukar_barang', not 'refund')
- **Sisa Utang: Rp 375.000** ✓
- Calculation: 375.000 - 0 - 0 = 375.000

**PB-20260514-0004**:
- Total: Rp 354.000
- Terbayar: Rp 0
- Refund: Rp 0 (has retur but jenis = 'tukar_barang', not 'refund')
- **Sisa Utang: Rp 354.000** ✓
- Calculation: 354.000 - 0 - 0 = 354.000

## How to Use

### 1. Create Retur with Refund Type
When creating retur pembelian:
- Select **Jenis Retur: "Refund (Pengembalian Uang)"**
- NOT "Tukar Barang"

### 2. Approve the Retur
Change status from "pending" to:
- "disetujui" (approved)
- "dikirim" (sent)
- "selesai" (completed)

### 3. Check Pelunasan Utang
- Open Pelunasan Utang page
- Select the pembelian
- You will see:
  - Total Pembelian: Rp xxx
  - Total Dibayar: Rp xxx
  - **Total Refund: Rp xxx** (will appear if > 0)
  - **Sisa Utang: Rp xxx** (already reduced by refund)

### 4. Make Payment
- The payment amount will auto-fill with the correct sisa utang (after refund deduction)
- Save the payment
- If total paid + refund >= total pembelian, status becomes "lunas"

## Important Notes

### Jenis Retur Differences

**Tukar Barang**:
- Barang rusak ditukar dengan barang baru
- Nilai pembelian tetap sama
- **Utang TIDAK berkurang**

**Refund**:
- Barang dikembalikan, uang dikembalikan
- Nilai pembelian berkurang
- **Utang BERKURANG**

### Status Requirements
Only these statuses reduce debt:
- ✅ disetujui (approved by vendor)
- ✅ dikirim (goods sent back)
- ✅ selesai (process completed)

These do NOT reduce debt:
- ❌ pending (waiting approval)
- ❌ ditolak (rejected)

## Files Modified

1. **app/Http/Controllers/PelunasanUtangController.php**
   - Fixed `create()` method filter
   - Fixed `store()` method sisa_pembayaran calculation

2. **app/Http/Controllers/ApSettlementController.php**
   - Fixed `store()` method
   - Fixed `update()` method
   - Fixed `destroy()` method

3. **resources/views/transaksi/pelunasan-utang/create.blade.php**
   - Fixed dropdown sisa utang calculation
   - Added refund breakdown in detail section
   - Updated JavaScript to display refund info

4. **app/Models/Pembelian.php** (already done in previous context)
   - Added `purchaseReturns()` relationship
   - Added `getTotalRefundAttribute()` accessor
   - Updated `getSisaUtangAttribute()` to include refund
   - Updated `syncPaymentStatus()` to consider refund

## Testing Checklist

- [x] Dropdown shows correct sisa utang (with refund deduction)
- [x] Detail section shows refund breakdown
- [x] Refund only counts jenis_retur = 'refund'
- [x] Refund only counts approved status
- [x] Tukar barang does NOT reduce debt
- [x] Payment saves with correct sisa_pembayaran
- [x] Status becomes lunas when paid + refund >= total
- [x] Existing data updated correctly

## Conclusion

**SISTEM SUDAH BEKERJA DENGAN BENAR! ✓**

Sisa utang sekarang otomatis berkurang ketika ada retur dengan jenis "refund" yang disetujui. Dropdown di halaman pelunasan utang sudah menampilkan sisa utang yang benar (sudah dikurangi refund).

Jika sisa utang tidak berkurang, pastikan:
1. Jenis retur = "refund" (bukan "tukar_barang")
2. Status retur = "disetujui", "dikirim", atau "selesai" (bukan "pending")
