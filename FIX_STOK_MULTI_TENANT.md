# Fix Stok Real-Time untuk Multi-Tenant

## Masalah yang Diperbaiki

### 1. Duplikasi Saldo Awal
**Masalah:** Stok ditampilkan 2x lipat dari yang seharusnya
- Database: `saldo_awal` = 12
- `stock_movements`: qty = 12 (initial_stock)
- Tampilan: 24 (salah! ❌)

**Penyebab:** Method `getStokRealTimeAttribute()` menambahkan `saldo_awal` + stock_movements
```php
// SALAH ❌
$totalStock = ($this->saldo_awal ?? 0) + $netStockFromMovements;
```

**Solusi:** Hanya hitung dari `stock_movements` karena saldo_awal sudah dimasukkan sebagai initial_stock
```php
// BENAR ✅
return $netStockFromMovements;
```

### 2. Multi-Tenant Isolation
**Masalah:** Query stock_movements tidak explicit filter by user_id

**Solusi:** Tambahkan explicit filter user_id untuk memastikan multi-tenant isolation
```php
$userId = $this->user_id ?? auth()->id();

$stockIn = StockMovement::where('item_type', 'material')
    ->where('item_id', $this->id)
    ->where('user_id', $userId)  // ✅ CRITICAL untuk multi-tenant
    ->where('direction', 'in')
    ->sum('qty');
```

## File yang Diubah

1. **app/Models/BahanBaku.php**
   - Method: `getStokRealTimeAttribute()`
   - Hapus penambahan saldo_awal
   - Tambah explicit filter user_id

2. **app/Models/BahanPendukung.php**
   - Method: `getStokRealTimeAttribute()`
   - Hapus penambahan saldo_awal
   - Tambah explicit filter user_id

## Cara Kerja Stok Real-Time (Setelah Fix)

```
Stok Real-Time = SUM(stock_in) - SUM(stock_out)

Dimana:
- stock_in termasuk: initial_stock, pembelian, produksi, adjustment in
- stock_out termasuk: penjualan, produksi (bahan), adjustment out
- Semua query HARUS filter by user_id untuk multi-tenant
```

## Testing

### Test Case 1: Bahan Baku Baru dengan Saldo Awal
```
Input:
- Nama: Jagung
- Saldo Awal: 12 kg
- user_id: 2

Expected:
- bahan_bakus.saldo_awal = 12
- stock_movements: 1 record (initial_stock, qty=12, user_id=2)
- Tampilan stok: 12 kg ✅

Actual (sebelum fix): 24 kg ❌
Actual (setelah fix): 12 kg ✅
```

### Test Case 2: Multi-Tenant Isolation
```
User A (id=1):
- Bahan: Tepung, saldo_awal=10
- Stok harus: 10 (hanya milik user A)

User B (id=2):
- Bahan: Tepung, saldo_awal=20
- Stok harus: 20 (hanya milik user B)

Tidak boleh tercampur! ✅
```

## Catatan Penting

1. **Saldo Awal** di tabel `bahan_bakus` dan `bahan_pendukungs` adalah untuk **referensi saja**
2. **Stok Real-Time** HARUS dihitung dari `stock_movements` untuk konsistensi
3. **Semua query** stock_movements HARUS filter by `user_id` untuk multi-tenant
4. **Global Scope** UserScope sudah ada, tapi kita tambah explicit filter untuk keamanan ekstra

## Tanggal
2026-05-06 21:00
