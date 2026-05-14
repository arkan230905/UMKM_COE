# Perbaikan Laporan Retur Pembelian - Total Retur dengan PPN

## Masalah yang Diperbaiki

Sebelumnya, kolom "Total Retur Pembelian" di laporan selalu menghitung PPN 11% meskipun pembelian tidak memiliki PPN. Seharusnya jika tidak ada PPN input (ppn_persen = 0), total retur sama dengan subtotal retur.

## Solusi yang Diterapkan

### 1. File yang Diubah

#### A. `app/Models/PurchaseReturn.php`

**Accessor `getPpnAmountAttribute()` (Line ~133-145)**

**SEBELUM:**
```php
public function getPpnAmountAttribute()
{
    return $this->total_retur * 0.11; // Hardcoded 11%
}
```

**SESUDAH:**
```php
public function getPpnAmountAttribute()
{
    // Get PPN percentage from pembelian, default to 0 if not set
    $ppnPersen = $this->pembelian->ppn_persen ?? 0;
    
    // Only calculate PPN if ppn_persen > 0
    if ($ppnPersen > 0) {
        return $this->total_retur * ($ppnPersen / 100);
    }
    
    return 0; // No PPN
}
```

**Perubahan:**
- Tidak lagi hardcoded 11%
- Mengambil ppn_persen dari relasi pembelian
- Default ke 0 jika tidak ada PPN
- Hanya menghitung PPN jika ppn_persen > 0

#### B. `resources/views/laporan/pembelian/partials/retur-content.blade.php`

**Kolom Total Retur (Line ~127-150)**

**SEBELUM:**
```php
@php
    $subtotal = $return->total_retur ?? 0;
    $ppnPersen = $return->pembelian->ppn_persen ?? 11; // Default 11%
    $ppnAmount = $subtotal * ($ppnPersen / 100);
    $totalWithPpn = $subtotal + $ppnAmount;
@endphp
<div class="small text-muted">
    Subtotal: Rp {{ number_format($subtotal, 0, ',', '.') }}<br>
    PPN {{ $ppnPersen }}%: Rp {{ number_format($ppnAmount, 0, ',', '.') }}
</div>
<strong class="text-primary">Rp {{ number_format($totalWithPpn, 0, ',', '.') }}</strong>
```

**SESUDAH:**
```php
@php
    $subtotal = $return->total_retur ?? 0;
    $ppnPersen = $return->pembelian->ppn_persen ?? 0; // Default 0
    
    // Only calculate PPN if ppn_persen > 0
    if ($ppnPersen > 0) {
        $ppnAmount = $subtotal * ($ppnPersen / 100);
        $totalWithPpn = $subtotal + $ppnAmount;
    } else {
        $ppnAmount = 0;
        $totalWithPpn = $subtotal; // No PPN, total = subtotal
    }
@endphp

@if($ppnPersen > 0)
    <div class="small text-muted">
        Subtotal: Rp {{ number_format($subtotal, 0, ',', '.') }}<br>
        PPN {{ $ppnPersen }}%: Rp {{ number_format($ppnAmount, 0, ',', '.') }}
    </div>
@endif

<strong class="text-primary">Rp {{ number_format($totalWithPpn, 0, ',', '.') }}</strong>

@if($ppnPersen == 0)
    <div class="small text-muted">Tanpa PPN</div>
@endif
```

**Perubahan:**
- Default ppn_persen ke 0 (bukan 11%)
- Conditional logic: hanya hitung PPN jika ppn_persen > 0
- Jika tidak ada PPN, total = subtotal
- Tampilkan breakdown PPN hanya jika ada PPN
- Tampilkan label "Tanpa PPN" jika tidak ada PPN

#### C. `resources/views/laporan/retur/index.blade.php`

**Kolom Total Retur (Line ~145-168)**

Perubahan yang sama seperti file B di atas.

### 2. Logic Baru yang Diterapkan

#### Rumus Perhitungan Total Retur:

**Jika Ada PPN (ppn_persen > 0):**
```
Subtotal = Total Retur (dari items)
PPN Amount = Subtotal × (ppn_persen / 100)
Total Retur Pembelian = Subtotal + PPN Amount
```

**Jika Tidak Ada PPN (ppn_persen = 0 atau null):**
```
Subtotal = Total Retur (dari items)
PPN Amount = 0
Total Retur Pembelian = Subtotal (sama dengan subtotal)
```

### 3. Contoh Kasus

#### Kasus 1: Pembelian dengan PPN 11%

**Data Pembelian:**
- Jagung: 20 kg × Rp 15.000 = Rp 300.000
- PPN: 11%

**Data Retur:**
- Jagung: 5 kg × Rp 15.000 = Rp 75.000

**Perhitungan:**
```
Subtotal Retur = Rp 75.000
PPN 11% = Rp 75.000 × 11% = Rp 8.250
Total Retur Pembelian = Rp 75.000 + Rp 8.250 = Rp 83.250
```

**Tampilan di Laporan:**
```
Subtotal: Rp 75.000
PPN 11%: Rp 8.250
─────────────────────
Rp 83.250
```

#### Kasus 2: Pembelian Tanpa PPN

**Data Pembelian:**
- Jagung: 20 kg × Rp 15.000 = Rp 300.000
- PPN: 0% (tidak ada PPN)

**Data Retur:**
- Jagung: 5 kg × Rp 15.000 = Rp 75.000

**Perhitungan:**
```
Subtotal Retur = Rp 75.000
PPN = 0
Total Retur Pembelian = Rp 75.000 (sama dengan subtotal)
```

**Tampilan di Laporan:**
```
Rp 75.000
Tanpa PPN
```

#### Kasus 3: Pembelian dengan PPN Custom (5%)

**Data Pembelian:**
- Jagung: 20 kg × Rp 15.000 = Rp 300.000
- PPN: 5%

**Data Retur:**
- Jagung: 5 kg × Rp 15.000 = Rp 75.000

**Perhitungan:**
```
Subtotal Retur = Rp 75.000
PPN 5% = Rp 75.000 × 5% = Rp 3.750
Total Retur Pembelian = Rp 75.000 + Rp 3.750 = Rp 78.750
```

**Tampilan di Laporan:**
```
Subtotal: Rp 75.000
PPN 5%: Rp 3.750
─────────────────────
Rp 78.750
```

### 4. Cara Testing

#### Test 1: Retur dari Pembelian dengan PPN 11%

1. Buat pembelian dengan PPN 11%
2. Buat retur dari pembelian tersebut
3. Buka Laporan Pembelian → Tab Retur
4. **Expected:** Total Retur = Subtotal + PPN 11%
5. **Expected:** Tampil breakdown: Subtotal, PPN 11%, Total

#### Test 2: Retur dari Pembelian Tanpa PPN

1. Buat pembelian dengan PPN 0% atau kosong
2. Buat retur dari pembelian tersebut
3. Buka Laporan Pembelian → Tab Retur
4. **Expected:** Total Retur = Subtotal (tidak ada tambahan PPN)
5. **Expected:** Tampil label "Tanpa PPN"
6. **Expected:** Tidak tampil breakdown PPN

#### Test 3: Retur dari Pembelian dengan PPN Custom

1. Buat pembelian dengan PPN 5%
2. Buat retur dari pembelian tersebut
3. Buka Laporan Pembelian → Tab Retur
4. **Expected:** Total Retur = Subtotal + PPN 5%
5. **Expected:** Tampil breakdown: Subtotal, PPN 5%, Total

### 5. Penyebab Masalah Sebelumnya

**Masalah 1: Hardcoded PPN 11% di Model**
- Accessor `getPpnAmountAttribute()` di model `PurchaseReturn` hardcoded 11%
- Tidak mengambil dari relasi pembelian
- Tidak ada conditional logic untuk cek apakah ada PPN atau tidak

**Masalah 2: Default PPN 11% di View**
- View menggunakan `$return->pembelian->ppn_persen ?? 11`
- Default ke 11% jika tidak ada PPN
- Seharusnya default ke 0

**Solusi:**
- Ubah accessor di model untuk mengambil dari pembelian
- Tambah conditional logic: hanya hitung PPN jika ppn_persen > 0
- Ubah default di view dari 11 ke 0
- Tampilkan breakdown PPN hanya jika ada PPN

### 6. Fitur yang Tidak Berubah

✅ Perhitungan subtotal retur tetap akurat
✅ Filter laporan tetap berfungsi
✅ Export PDF tetap berfungsi
✅ Pagination tetap berfungsi
✅ Status badge tetap ditampilkan
✅ Detail item retur tetap ditampilkan

### 7. Catatan Penting

1. **PPN diambil dari pembelian asli**, bukan dari retur
2. **Jika pembelian tidak ada PPN (0%), retur juga tidak ada PPN**
3. **Total Retur Pembelian = Subtotal + PPN** (jika ada PPN)
4. **Total Retur Pembelian = Subtotal** (jika tidak ada PPN)
5. **Accessor di model otomatis digunakan** oleh controller untuk menghitung total

### 8. Debugging

Jika ada masalah, cek:

1. **Database pembelian:**
   ```sql
   SELECT id, nomor_pembelian, ppn_persen FROM pembelians WHERE id = ?;
   ```

2. **Database retur:**
   ```sql
   SELECT id, return_number, pembelian_id, total_retur FROM purchase_returns WHERE id = ?;
   ```

3. **Accessor di Model:**
   ```php
   $retur = PurchaseReturn::find(1);
   dd([
       'total_retur' => $retur->total_retur,
       'ppn_persen' => $retur->pembelian->ppn_persen,
       'ppn_amount' => $retur->ppn_amount,
       'total_with_ppn' => $retur->total_with_ppn
   ]);
   ```

### 9. Commit Message

```
fix: Perbaiki perhitungan total retur pembelian - sesuaikan dengan PPN pembelian

- Ubah accessor getPpnAmountAttribute() untuk ambil PPN dari pembelian
- Jika tidak ada PPN (ppn_persen = 0), total = subtotal
- Jika ada PPN, total = subtotal + PPN
- Update view untuk conditional display breakdown PPN
- Tambah label "Tanpa PPN" untuk retur tanpa PPN
- Ubah default ppn_persen dari 11% ke 0%
```

## Status: ✅ SELESAI

Semua requirement telah diimplementasikan:
- ✅ Jika tidak ada PPN, total retur = subtotal
- ✅ Jika ada PPN, total retur = subtotal + PPN
- ✅ PPN diambil dari pembelian asli
- ✅ Tampilan conditional (tampil breakdown hanya jika ada PPN)
- ✅ Label "Tanpa PPN" untuk retur tanpa PPN
- ✅ Tidak mengubah database/migration
