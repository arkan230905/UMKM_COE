# Sisa Utang Calculation Fix

## Problem
Sisa utang menunjukkan nilai yang salah dan ada duplikasi utang:
- Total Pembelian: Rp 2.220.000
- Sisa Utang: Rp 3.940.000 (seharusnya Rp 2.220.000)
- Ada 2 utang padahal seharusnya hanya 1 (pembelian transfer seharusnya lunas)

## Root Causes

### 1. Negative Terbayar Values
Field `terbayar` di database memiliki nilai negatif (-1.720.000), sehingga:
- Sisa Utang = Total - Terbayar
- Sisa Utang = 2.220.000 - (-1.720.000) = 3.940.000 ❌

### 2. Cash/Transfer Payments Not Marked as Lunas
Pembelian dengan payment method `cash` atau `transfer` seharusnya otomatis lunas, tapi statusnya masih `belum_lunas`.

## Solution

### 1. Fixed Negative Terbayar Values
- Identified pembelian with negative `terbayar` values
- Set `terbayar` to 0 for records without pelunasan
- Recalculated from pelunasan records for records with pelunasan

### 2. Added syncPaymentStatus() Method
**File**: `app/Models/Pembelian.php`

**Logic**:
```php
// For cash/transfer payments, mark as lunas immediately
if ($this->payment_method === 'cash' || $this->payment_method === 'transfer') {
    $this->terbayar = $this->total_harga;
    $this->sisa_pembayaran = 0;
    $this->status = 'lunas';
}

// For credit payments, calculate from pelunasan records
$totalPelunasan = $this->pelunasan()->sum('jumlah');
$this->terbayar = $totalPelunasan;
$this->sisa_pembayaran = ($this->total_harga ?? 0) - $totalPelunasan;
```

### 3. Updated Sync Command
**File**: `app/Console/Commands/SyncPembelianPaymentStatus.php`

**Usage**:
```bash
php artisan pembelian:sync-payment-status
```

### 4. Data Now Correct
After fix:
- **PB-20260419-0001** (Transfer): Rp 1.776.000 - **Lunas** ✓
- **PB-20260420-0001** (Kredit): Rp 2.220.000 - **Belum Lunas** ✓

Only 1 utang remaining (credit purchase)

## Prevention

To prevent this issue in the future:

1. **Always use the sync command** after bulk operations on pembelian or pelunasan
2. **Use the Pembelian model accessor** `sisa_utang` instead of `sisa_pembayaran` field:
   ```php
   // Good - uses accessor that calculates from pelunasan
   $sisaUtang = $pembelian->sisa_utang;
   
   // Avoid - uses database field that might be out of sync
   $sisaUtang = $pembelian->sisa_pembayaran;
   ```

3. **In forms, use the accessor**:
   ```php
   // In controller
   $sisaUtang = $pembelian->sisa_utang; // Uses accessor
   
   // In view
   <p>Sisa Utang: Rp {{ number_format($sisaUtang, 0, ',', '.') }}</p>
   ```

## Files Modified/Created

### Created
- `app/Console/Commands/SyncPembelianPaymentStatus.php` - Command to sync payment status

### Modified
- `app/Models/Pembelian.php` - Added syncPaymentStatus() method

## Testing

Run the sync command to verify:
```bash
php artisan pembelian:sync-payment-status
```

Expected output:
```
🔄 Syncing pembelian payment status...
Updating Pembelian ID 1 (PB-20260419-0001):
  - Payment Method: transfer
  - Terbayar: Rp 0 → Rp 1.776.000
  - Status: belum_lunas → lunas
✅ Synced 1 pembelian records!
```

## Related Models

### Pembelian Model Accessors
- `sisa_utang`: Calculates from total_harga - total_dibayar (from pelunasan)
- `total_dibayar`: Sum of all pelunasan amounts
- `status_pembayaran`: Returns payment status (Belum Bayar, Sebagian, Lunas)

These accessors are more reliable than database fields because they calculate from actual pelunasan records.
