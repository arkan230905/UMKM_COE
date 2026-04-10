# IMPLEMENTASI RETUR PEMBELIAN KE KARTU STOK

## Overview
Implementasi ini menambahkan pencatatan otomatis ke tabel `kartu_stok` (stock_movements) saat update status retur pembelian, sesuai dengan requirement:

### 1. Logic Pencatatan
- **Status = "dikirim"**: Insert stok keluar (OUT) dengan keterangan "Retur Pembelian - Dikirim ke Vendor"
- **Jenis = "tukar_barang" + Status = "selesai"**: Insert stok masuk (IN) dengan keterangan "Retur Tukar Barang - Barang Diterima"

### 2. Support Multi-Material
- ✅ Bahan Baku (`item_type = 'material'`)
- ✅ Bahan Pendukung (`item_type = 'support'`)
- ✅ Loop untuk setiap item dalam retur

### 3. Pencegahan Duplikasi
- ✅ Check existing record sebelum insert
- ✅ Skip jika sudah ada record dengan parameter yang sama

## Files Modified

### 1. Migration: `database/migrations/2026_04_10_000001_update_stock_movements_for_retur.php`
**Perubahan:**
- Menambahkan field `keterangan` untuk deskripsi retur
- Update enum `item_type` untuk include 'support' (bahan pendukung)

```sql
ALTER TABLE stock_movements ADD COLUMN keterangan VARCHAR(255) NULL;
ALTER TABLE stock_movements MODIFY COLUMN item_type ENUM('material', 'product', 'support');
```

### 2. Controller: `app/Http/Controllers/ReturController.php`
**Method yang diupdate:**
- `handleStatusChange()` - Logic pencatatan stock movements
- `recordStockMovement()` - Method baru untuk record ke tabel

**Perubahan utama:**
```php
// Saat status = "dikirim" - Kurangi stok
if ($newStatus === \App\Models\PurchaseReturn::STATUS_DIKIRIM) {
    foreach ($retur->items as $item) {
        // Handle bahan baku
        if ($item->bahan_baku_id && $item->bahanBaku) {
            $this->recordStockMovement(
                'material',
                $item->bahan_baku_id,
                $retur->return_date ?? now(),
                'out',
                $item->quantity,
                $item->unit ?? $material->satuan->nama ?? 'KG',
                $item->unit_price ?? 0,
                $item->subtotal ?? 0,
                'purchase_return',
                $retur->id,
                "Retur Pembelian - Dikirim ke Vendor"
            );
        }
        
        // Handle bahan pendukung
        if ($item->bahan_pendukung_id && $item->bahanPendukung) {
            $this->recordStockMovement(
                'support',
                $item->bahan_pendukung_id,
                // ... parameter sama
                "Retur Pembelian - Dikirim ke Vendor"
            );
        }
    }
}

// Saat jenis = "tukar_barang" + status = "selesai" - Tambah stok
if ($retur->jenis_retur === \App\Models\PurchaseReturn::JENIS_TUKAR_BARANG && 
    $newStatus === \App\Models\PurchaseReturn::STATUS_SELESAI) {
    // ... logic sama dengan direction = 'in'
    // keterangan = "Retur Tukar Barang - Barang Diterima"
}
```

## Struktur Data

### Tabel: `stock_movements`
| Field | Type | Example Value |
|-------|------|---------------|
| `item_type` | enum | 'material', 'support' |
| `item_id` | bigint | ID bahan baku/pendukung |
| `tanggal` | date | '2026-04-10' |
| `direction` | enum | 'in', 'out' |
| `qty` | decimal | 15.0000 |
| `satuan` | varchar | 'Ekor', 'KG', 'Liter' |
| `unit_cost` | decimal | 45000.0000 |
| `total_cost` | decimal | 675000.00 |
| `ref_type` | varchar | 'purchase_return' |
| `ref_id` | bigint | ID retur pembelian |
| `keterangan` | varchar | 'Retur Pembelian - Dikirim ke Vendor' |

## Testing

### Test File: `test_retur_stock_integration.php`
Script untuk memverifikasi implementasi:

```bash
php test_retur_stock_integration.php
```

**Output yang diharapkan:**
```
=== TEST RETUR STOCK INTEGRATION ===

✅ Found retur untuk testing:
   - ID: 8
   - Return Number: PRTN-20260410-0001
   - Status: pending
   - Jenis: tukar_barang
   - Items: 1

=== TESTING STATUS UPDATE KE 'DIKIRIM' ===
✅ Created OUT movement for Bahan Baku: Ayam Potong (Qty: 15.0000)

=== TESTING TUKAR BARANG SELESAI ===
✅ Created IN movement for Bahan Baku: Ayam Potong (Qty: 15.0000)

=== VERIFIKASI HASIL AKHIR ===
Total stock movements untuk retur ini: 2

- 2026-04-10 | out | 15.0000 Ekor | Ayam Potong | Retur Pembelian - Dikirim ke Vendor
- 2026-04-10 | in | 15.0000 Ekor | Ayam Potong | Retur Tukar Barang - Barang Diterima

✅ TEST COMPLETED SUCCESSFULLY!
```

## Workflow User

### 1. Buat Retur Pembelian
- User buat retur dari halaman pembelian
- Status awal: `pending`
- Belum ada pencatatan stok

### 2. Klik "Kirim Barang" (Status → dikirim)
- ✅ **Otomatis insert ke stock_movements**
- Direction: `out` (stok keluar)
- Keterangan: "Retur Pembelian - Dikirim ke Vendor"
- **Muncul di laporan kartu stok sebagai stok keluar**

### 3. Klik "Barang Diterima" (Tukar Barang → selesai)
- ✅ **Otomatis insert ke stock_movements**
- Direction: `in` (stok masuk)
- Keterangan: "Retur Tukar Barang - Barang Diterima"
- **Muncul di laporan kartu stok sebagai stok masuk**

## Verifikasi di Laporan Kartu Stok

### Query untuk cek data:
```sql
SELECT 
    sm.tanggal,
    sm.direction,
    sm.qty,
    sm.satuan,
    sm.keterangan,
    CASE 
        WHEN sm.item_type = 'material' THEN bb.nama_bahan
        WHEN sm.item_type = 'support' THEN bp.nama_bahan
    END as nama_material
FROM stock_movements sm
LEFT JOIN bahan_bakus bb ON sm.item_id = bb.id AND sm.item_type = 'material'
LEFT JOIN bahan_pendukungs bp ON sm.item_id = bp.id AND sm.item_type = 'support'
WHERE sm.ref_type = 'purchase_return'
ORDER BY sm.tanggal DESC;
```

### Akses Laporan:
1. Menu: **Laporan → Kartu Stok**
2. Pilih jenis material dan material spesifik
3. Klik "Tampilkan"
4. **Data retur akan muncul di kolom yang sesuai:**
   - Stok keluar saat "Kirim Barang"
   - Stok masuk saat "Barang Diterima" (tukar barang)

## Troubleshooting

### 1. Data tidak muncul di laporan
- Cek apakah migration sudah dijalankan: `php artisan migrate`
- Cek log Laravel untuk error: `storage/logs/laravel.log`
- Jalankan test script untuk verifikasi

### 2. Error "Column 'keterangan' doesn't exist"
- Jalankan migration: `php artisan migrate --path=database/migrations/2026_04_10_000001_update_stock_movements_for_retur.php`

### 3. Error "Invalid enum value 'support'"
- Migration enum belum dijalankan
- Cek struktur tabel: `DESCRIBE stock_movements;`

## Summary

✅ **COMPLETED REQUIREMENTS:**
1. ✅ Pencatatan ke tabel kartu_stok saat update status retur
2. ✅ Status "dikirim" → stok keluar dengan keterangan yang tepat
3. ✅ Jenis "tukar_barang" + status "selesai" → stok masuk
4. ✅ Menggunakan field yang sama dengan laporan stok
5. ✅ Data masuk ke tabel yang sama (stock_movements)
6. ✅ Tidak terjadi double insert (ada check duplikasi)
7. ✅ Support bahan baku dan bahan pendukung
8. ✅ Muncul di laporan kartu stok sesuai workflow

**HASIL:**
- Setelah klik "Kirim Barang" → ✅ muncul stok keluar di laporan
- Setelah klik "Barang Diterima" → ✅ muncul stok masuk di laporan