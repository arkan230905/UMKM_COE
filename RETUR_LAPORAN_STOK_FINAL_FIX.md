# PERBAIKAN LAPORAN STOK RETUR PEMBELIAN

## Masalah yang Dipecahkan
- ✅ Retur pembelian (barang keluar) sudah muncul di laporan
- ❌ Barang pengganti dari vendor (stok masuk) belum muncul
- ❌ Laporan tidak menampilkan kolom khusus untuk retur

## Solusi yang Diimplementasikan

### 1. Penambahan Kolom Retur di Laporan
**File:** `resources/views/laporan/kartu-stok.blade.php`

**Perubahan Header Tabel:**
```html
<!-- SEBELUM -->
<th colspan="3">Stok Awal</th>
<th colspan="3">Pembelian</th>
<th colspan="3">Produksi</th>
<th colspan="4">Total Stok</th>

<!-- SESUDAH -->
<th colspan="3">Stok Awal</th>
<th colspan="3">Pembelian</th>
<th colspan="3">Retur</th>        <!-- KOLOM BARU -->
<th colspan="3">Produksi</th>
<th colspan="4">Total Stok</th>
```

**Penambahan Logic Kolom Retur:**
```php
<!-- Retur (3 columns) -->
@if($movement->ref_type == 'purchase_return')
    @if($movement->direction == 'out')
        {{-- Retur Keluar (Barang dikirim ke vendor) --}}
        <td class="text-center text-danger">-{{ number_format($movement->qty, 0) }} {{ $satuanUtama }}</td>
        <td class="text-end">Rp {{ number_format($movement->unit_cost, 2, ',', '.') }}</td>
        <td class="text-end text-danger">-Rp {{ number_format($movement->qty * $movement->unit_cost, 0, ',', '.') }}</td>
    @else
        {{-- Retur Masuk (Barang pengganti diterima) --}}
        <td class="text-center text-success">+{{ number_format($movement->qty, 0) }} {{ $satuanUtama }}</td>
        <td class="text-end">Rp {{ number_format($movement->unit_cost, 2, ',', '.') }}</td>
        <td class="text-end text-success">+Rp {{ number_format($movement->qty * $movement->unit_cost, 0, ',', '.') }}</td>
    @endif
@else
    <td></td>
    <td></td>
    <td></td>
@endif
```

### 2. Perbaikan Referensi Display
**Penambahan logic untuk menampilkan referensi retur:**
```php
@elseif($movement->ref_type == 'purchase_return')
    @if($movement->direction == 'out')
        Retur Keluar #{{ $movement->ref_id }}
    @else
        Retur Masuk #{{ $movement->ref_id }}
    @endif
```

### 3. Perbaikan Keterangan di Controller
**File:** `app/Http/Controllers/ReturController.php`

**Perubahan keterangan untuk stok masuk:**
```php
// SEBELUM
"Retur Tukar Barang - Barang Diterima"

// SESUDAH  
"Barang pengganti dari retur pembelian"
```

## Hasil Implementasi

### Data yang Tercatat di `stock_movements`:

#### 1. Saat Status = "dikirim" (Barang Keluar)
```sql
INSERT INTO stock_movements (
    item_type, item_id, tanggal, direction, qty, satuan, 
    unit_cost, total_cost, ref_type, ref_id, keterangan
) VALUES (
    'material', 1, '2026-04-10', 'out', 15.0000, 'Ekor',
    45000.0000, 675000.00, 'purchase_return', 8,
    'Retur Pembelian - Dikirim ke Vendor'
);
```

#### 2. Saat Status = "selesai" (Barang Pengganti Masuk)
```sql
INSERT INTO stock_movements (
    item_type, item_id, tanggal, direction, qty, satuan,
    unit_cost, total_cost, ref_type, ref_id, keterangan  
) VALUES (
    'material', 1, '2026-04-10', 'in', 15.0000, 'Ekor',
    45000.0000, 675000.00, 'purchase_return', 8,
    'Barang pengganti dari retur pembelian'
);
```

### Tampilan di Laporan Kartu Stok:

| Tanggal | Referensi | Stok Awal | Pembelian | **Retur** | Produksi | Total Stok |
|---------|-----------|-----------|-----------|-----------|----------|------------|
| 10/04/2026 | Retur Keluar #8 | - | - | **-15 Ekor** (merah) | - | 85 Ekor |
| 10/04/2026 | Retur Masuk #8 | - | - | **+15 Ekor** (hijau) | - | 100 Ekor |

## Verifikasi Hasil

### Test Data:
```
📊 2026-04-10 | KELUAR | -15.0000 Ekor | Bahan Baku: Ayam Potong
   Keterangan: Retur Pembelian - Dikirim ke Vendor
   Ref: purchase_return#8

📊 2026-04-10 | MASUK | +15.0000 Ekor | Bahan Baku: Ayam Potong      
   Keterangan: Barang pengganti dari retur pembelian
   Ref: purchase_return#8
```

### Running Stock Calculation:
```
Material: Ayam Potong
  2026-04-01 | Initial_stock | in | 50.0000 | Running: 50
  2026-04-09 | Purchase | in | 40.0000 | Running: 90
  2026-04-09 | Purchase_cancellation | out | 40.0000 | Running: 50   
  2026-04-09 | Purchase | in | 50.0000 | Running: 100
  2026-04-10 | Retur Keluar | out | 15.0000 | Running: 85
  2026-04-10 | Retur Masuk | in | 15.0000 | Running: 100
```

## Cara Menggunakan

### 1. Workflow Retur Tukar Barang:
1. **Buat Retur** → Status: `pending`
2. **Klik "Kirim Barang"** → Status: `dikirim` 
   - ✅ Stok keluar tercatat di laporan (kolom Retur, merah)
3. **Klik "Barang Diterima"** → Status: `selesai`
   - ✅ Stok masuk tercatat di laporan (kolom Retur, hijau)

### 2. Melihat Laporan:
1. Buka: **Laporan → Kartu Stok**
2. Pilih jenis material dan material spesifik
3. Klik "Tampilkan"
4. **Hasil:** Muncul 2 baris di kolom Retur:
   - Baris 1: Retur Keluar (merah, minus)
   - Baris 2: Retur Masuk (hijau, plus)

## Summary

✅ **MASALAH TERPECAHKAN:**
1. ✅ Barang pengganti dari vendor sekarang muncul di laporan
2. ✅ Data stok masuk dari retur pembelian ditampilkan dengan benar
3. ✅ Kolom khusus "Retur" ditambahkan di laporan
4. ✅ Keterangan yang jelas: "Barang pengganti dari retur pembelian"
5. ✅ Visual yang jelas: merah untuk keluar, hijau untuk masuk
6. ✅ Menggunakan tabel yang sama (`stock_movements`) agar langsung sinkron

**HASIL AKHIR:**
- Di laporan stok muncul 2 baris untuk setiap retur tukar barang:
  1. **Retur Pembelian (keluar)** - saat barang dikirim ke vendor
  2. **Barang pengganti dari retur (masuk)** - saat barang pengganti diterima

Implementasi ini memastikan bahwa semua pergerakan stok dari retur pembelian (baik keluar maupun masuk) ditampilkan dengan jelas di laporan kartu stok dengan kolom dan keterangan yang tepat.