# Implementasi Integrasi Retur Pembelian dengan Kartu Stok

## 📋 Overview

Implementasi ini menambahkan logic untuk mencatat pergerakan stok retur pembelian ke tabel `stock_movements` secara otomatis saat status retur berubah.

## 🎯 Tujuan

1. **Saat status = "dikirim"**: Mencatat pengurangan stok (qty_keluar)
2. **Saat jenis_retur = "tukar_barang" dan status = "selesai"**: Mencatat penambahan stok (qty_masuk)
3. **Audit Trail**: Semua pergerakan stok tercatat dengan keterangan yang jelas
4. **Laporan Stok**: Data langsung muncul di laporan kartu stok

## 🔄 Logic Workflow

### 1. Status "dikirim" (Semua Jenis Retur)

```
Status: pending/disetujui → dikirim
Action: Kurangi stok + Record stock_movements
```

**Stock Movement Record:**
- `direction`: 'out'
- `qty`: quantity dari retur item
- `keterangan`: "Retur Pembelian - Dikirim ke Vendor"
- `ref_type`: 'purchase_return'
- `ref_id`: ID retur

### 2. Status "selesai" (Khusus Tukar Barang)

```
Status: diproses → selesai (jenis_retur = 'tukar_barang')
Action: Tambah stok + Record stock_movements
```

**Stock Movement Record:**
- `direction`: 'in'
- `qty`: quantity dari retur item
- `keterangan`: "Retur Tukar Barang - Barang Diterima"
- `ref_type`: 'purchase_return'
- `ref_id`: ID retur

## 📁 File yang Dimodifikasi

### 1. Controller: `app/Http/Controllers/ReturController.php`

**Method yang diupdate:**
- `handleStatusChange()` - Ditambahkan logic untuk record stock movements
- `recordStockMovement()` - Method baru untuk mencatat ke tabel stock_movements

**Perubahan utama:**
```php
// Saat status = dikirim
if ($newStatus === \App\Models\PurchaseReturn::STATUS_DIKIRIM) {
    foreach ($retur->items as $item) {
        // Update stok material
        $material->updateStok($qty, 'out', "Retur dikirim ID: {$retur->id}");
        
        // Record ke stock_movements
        $this->recordStockMovement(
            'material',
            $item->bahan_baku_id,
            $retur->return_date ?? now(),
            'out',
            $qty,
            $item->unit,
            $item->unit_price,
            $item->subtotal,
            'purchase_return',
            $retur->id,
            "Retur Pembelian - Dikirim ke Vendor"
        );
    }
}

// Saat tukar barang selesai
if ($retur->jenis_retur === 'tukar_barang' && $newStatus === 'selesai') {
    foreach ($retur->items as $item) {
        // Update stok material
        $material->updateStok($qty, 'in', "Tukar barang selesai ID: {$retur->id}");
        
        // Record ke stock_movements
        $this->recordStockMovement(
            'material',
            $item->bahan_baku_id,
            $retur->return_date ?? now(),
            'in',
            $qty,
            $item->unit,
            $item->unit_price,
            $item->subtotal,
            'purchase_return',
            $retur->id,
            "Retur Tukar Barang - Barang Diterima"
        );
    }
}
```

### 2. Model: `app/Models/StockMovement.php`

**Perubahan:**
- Ditambahkan field `keterangan` ke `$fillable`

### 3. Migration: `database/migrations/2026_04_10_154234_add_keterangan_to_stock_movements_table.php`

**Kolom baru:**
```php
$table->text('keterangan')->nullable()->after('ref_id');
```

## 🗃️ Struktur Data Stock Movements

### Tabel: `stock_movements`

| Field | Type | Description |
|-------|------|-------------|
| `item_type` | enum | 'material', 'product', 'support' |
| `item_id` | bigint | ID bahan baku/pendukung |
| `tanggal` | date | Tanggal transaksi |
| `direction` | enum | 'in' (masuk), 'out' (keluar) |
| `qty` | decimal(15,4) | Jumlah quantity |
| `satuan` | varchar(50) | Unit satuan |
| `unit_cost` | decimal(15,4) | Harga per unit |
| `total_cost` | decimal(15,2) | Total nilai |
| `ref_type` | varchar(50) | 'purchase_return' |
| `ref_id` | bigint | ID retur |
| `keterangan` | text | Deskripsi transaksi |

### Contoh Data

**Saat Kirim Barang (Status: dikirim)**
```sql
INSERT INTO stock_movements VALUES (
    'material',           -- item_type
    2,                   -- item_id (bahan_baku_id)
    '2026-04-10',        -- tanggal
    'out',               -- direction
    15.0000,             -- qty
    'Ekor',              -- satuan
    50000.00,            -- unit_cost
    750000.00,           -- total_cost
    'purchase_return',   -- ref_type
    8,                   -- ref_id (retur_id)
    'Retur Pembelian - Dikirim ke Vendor' -- keterangan
);
```

**Saat Barang Diterima (Status: selesai, tukar_barang)**
```sql
INSERT INTO stock_movements VALUES (
    'material',           -- item_type
    2,                   -- item_id (bahan_baku_id)
    '2026-04-10',        -- tanggal
    'in',                -- direction
    15.0000,             -- qty
    'Ekor',              -- satuan
    50000.00,            -- unit_cost
    750000.00,           -- total_cost
    'purchase_return',   -- ref_type
    8,                   -- ref_id (retur_id)
    'Retur Tukar Barang - Barang Diterima' -- keterangan
);
```

## 🧪 Testing

### 1. Setup Test Data

```bash
# Reset retur status untuk testing
php reset_retur_status.php

# Check current condition
php test_retur_stock_integration.php
```

### 2. Manual Testing Steps

1. **Buka halaman retur**: `http://127.0.0.1:8000/transaksi/retur-pembelian`
2. **Klik tombol "ACC Vendor"** untuk retur ID 8
3. **Klik tombol "Kirim Barang"** → Status menjadi "dikirim"
4. **Periksa stock movements**:
   ```sql
   SELECT * FROM stock_movements 
   WHERE ref_type = 'purchase_return' AND ref_id = 8;
   ```
5. **Periksa stok material**:
   ```sql
   SELECT nama_bahan, stok FROM bahan_bakus WHERE id = 2;
   ```

### 3. Expected Results

**Setelah "Kirim Barang":**
- Stok Ayam Potong berkurang 15 Ekor
- Ada record di `stock_movements` dengan `direction = 'out'`
- Keterangan: "Retur Pembelian - Dikirim ke Vendor"

**Setelah "Barang Diterima" (tukar_barang):**
- Stok Ayam Potong bertambah 15 Ekor
- Ada record di `stock_movements` dengan `direction = 'in'`
- Keterangan: "Retur Tukar Barang - Barang Diterima"

## 🔍 Verification Queries

### Check Stock Movements
```sql
SELECT 
    sm.tanggal,
    sm.direction,
    sm.qty,
    sm.satuan,
    sm.keterangan,
    bb.nama_bahan
FROM stock_movements sm
LEFT JOIN bahan_bakus bb ON sm.item_id = bb.id
WHERE sm.ref_type = 'purchase_return'
ORDER BY sm.tanggal DESC;
```

### Check Material Stock
```sql
SELECT 
    nama_bahan,
    stok,
    s.nama as satuan
FROM bahan_bakus bb
LEFT JOIN satuans s ON bb.satuan_id = s.id
WHERE bb.id IN (
    SELECT DISTINCT item_id 
    FROM stock_movements 
    WHERE ref_type = 'purchase_return'
);
```

## 🚨 Important Notes

### 1. Prevent Double Insert
- Logic menggunakan status change sebagai trigger
- Tidak akan double insert jika status tidak berubah
- Transaction rollback jika ada error

### 2. Support Multiple Materials
- Mendukung bahan_baku dan bahan_pendukung
- Loop untuk setiap item dalam retur
- Validasi material exists sebelum update

### 3. Error Handling
- Try-catch untuk record stock movement
- Log error jika gagal
- Transaction rollback untuk data consistency

### 4. Audit Trail
- Semua pergerakan tercatat dengan timestamp
- Keterangan yang jelas untuk setiap transaksi
- Reference ke retur asli (ref_type, ref_id)

## 📊 Impact on Reports

### Laporan Kartu Stok
- Data retur otomatis muncul di laporan
- Terpisah berdasarkan direction (in/out)
- Keterangan yang jelas untuk audit

### Laporan Stok Real-time
- Stok material terupdate real-time
- Konsisten dengan stock movements
- Mendukung FIFO/LIFO calculation

## 🔧 Maintenance

### Rollback Retur (Jika Diperlukan)
```sql
-- Delete stock movements
DELETE FROM stock_movements 
WHERE ref_type = 'purchase_return' AND ref_id = [RETUR_ID];

-- Manual adjust stock if needed
UPDATE bahan_bakus SET stok = [CORRECT_AMOUNT] WHERE id = [MATERIAL_ID];
```

### Monitor Performance
```sql
-- Check stock movements count
SELECT COUNT(*) FROM stock_movements WHERE ref_type = 'purchase_return';

-- Check for orphaned records
SELECT * FROM stock_movements sm
WHERE sm.ref_type = 'purchase_return'
AND NOT EXISTS (
    SELECT 1 FROM purchase_returns pr WHERE pr.id = sm.ref_id
);
```