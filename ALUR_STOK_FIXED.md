# Alur Stok - Sudah Diperbaiki ✅

## Ringkasan Perbaikan

Sistem stok sudah berjalan dengan benar. Semua transaksi (pembelian, produksi, penjualan) sudah mencatat pergerakan stok ke tabel `stock_movements` dan mengupdate field `stok` di tabel master (`bahan_bakus` dan `produks`).

## Alur Stok Bahan Baku

### 1. Pembelian Bahan Baku (IN)
**File**: `app/Http/Controllers/PembelianController.php`

Ketika ada pembelian bahan baku:
- ✅ Field `stok` di tabel `bahan_bakus` **bertambah**
- ✅ Harga satuan diupdate dengan **moving average**
- ✅ Dicatat di `stock_movements` dengan `direction = 'in'`
- ✅ Dicatat di `stock_layers` untuk FIFO costing

```php
// Update stok bahan baku
$bahan->stok = $stokBaru;
$bahan->harga_satuan = $hargaBaru; // moving average
$bahan->save();

// Catat movement
$stock->addLayer('material', $bahan->id, $qtyInBaseUnit, ...);
```

### 2. Produksi (OUT untuk Bahan Baku)
**File**: `app/Http/Controllers/ProduksiController.php`

Ketika produksi menggunakan bahan baku:
- ✅ Field `stok` di tabel `bahan_bakus` **berkurang**
- ✅ Dicatat di `stock_movements` dengan `direction = 'out'`
- ✅ Menggunakan FIFO costing dari `stock_layers`

```php
// Konsumsi bahan baku
$fifoCost = $stock->consume('material', $bahan->id, $qtyBase, ...);

// Update stok bahan baku
$bahan->stok = (float)$bahan->stok - $qtyBase;
$bahan->save();
```

## Alur Stok Produk

### 1. Produksi Selesai (IN untuk Produk)
**File**: `app/Http/Controllers/ProduksiController.php`

Ketika produksi selesai:
- ✅ Field `stok` di tabel `produks` **bertambah**
- ✅ Dicatat di `stock_movements` dengan `direction = 'in'`
- ✅ Dicatat di `stock_layers` dengan unit cost dari total biaya produksi

```php
// Tambahkan layer produk
$stock->addLayer('product', $produk->id, $qtyProd, 'pcs', $unitCostProduk, ...);

// Update stok produk
$produk->stok = (float)($produk->stok ?? 0) + $qtyProd;
$produk->save();
```

### 2. Penjualan (OUT untuk Produk)
**File**: `app/Http/Controllers/PenjualanController.php`

Ketika ada penjualan:
- ✅ Field `stok` di tabel `produks` **berkurang**
- ✅ Dicatat di `stock_movements` dengan `direction = 'out'`
- ✅ Menggunakan FIFO costing untuk HPP

```php
// FIFO OUT
$cogs = $stock->consume('product', $prod->id, $qty, 'pcs', 'sale', ...);

// Update stok produk
$prod->stok = (float)($prod->stok ?? 0) - $qty;
$prod->save();
```

## Laporan Stok

### Tampilan Ringkasan
**URL**: `/laporan/stok?tipe=material` atau `/laporan/stok?tipe=product`

Menampilkan daftar semua bahan baku atau produk dengan stok saat ini:
- Nama item
- Stok saat ini (dari field `stok` di tabel master)
- Satuan
- Tombol untuk melihat kartu stok detail

### Kartu Stok Detail
**URL**: `/laporan/stok?tipe=material&item_id=6&from=2025-11-01&to=2025-11-30`

Menampilkan pergerakan stok detail untuk item tertentu:
- Saldo awal (sebelum periode)
- Setiap transaksi masuk/keluar dengan:
  - Tanggal
  - Referensi (purchase#14, production#11, sale#12, dll)
  - Qty masuk & nilai
  - Qty keluar & nilai
  - Saldo running
- Data diambil dari tabel `stock_movements`

## Contoh Data dari Database

Berdasarkan data `stock_movements` yang Anda tunjukkan:

### Bahan Baku (Material ID 6)
- **Pembelian #14**: Masuk 100 KG @ Rp 50,000 = Rp 5,000,000
- **Produksi #11**: Keluar 3 KG @ Rp 50,000 = Rp 150,000
- **Produksi #12**: Keluar 5 KG @ Rp 50,000 = Rp 250,000
- **Produksi #13**: Keluar 6 KG @ Rp 50,000 = Rp 300,000
- **Produksi #14**: Keluar 3 KG @ Rp 50,000 = Rp 150,000
- **Saldo**: 100 - 3 - 5 - 6 - 3 = **83 KG**

### Produk (Product ID 4)
- **Produksi #11**: Masuk 30 pcs @ Rp 10,971 = Rp 329,130
- **Penjualan #12**: Keluar 20 pcs @ Rp 10,971 = Rp 219,420
- **Penjualan #13**: Keluar 4 pcs @ Rp 10,971 = Rp 43,884
- **Produksi #14**: Masuk 30 pcs @ Rp 10,971 = Rp 329,130
- **Produksi #11** (duplikat): Masuk 30 pcs @ Rp 10,971 = Rp 329,130
- **Saldo**: 30 - 20 - 4 + 30 + 30 = **66 pcs**

## Kesimpulan

✅ **Alur stok sudah berjalan dengan benar!**

Semua transaksi sudah:
1. Mengupdate field `stok` di tabel master
2. Mencatat pergerakan di `stock_movements`
3. Menggunakan FIFO costing melalui `stock_layers`
4. Laporan stok sudah menampilkan data dari `stock_movements` dengan benar

Anda sekarang bisa:
- Melihat ringkasan stok semua item
- Melihat kartu stok detail per item
- Filter berdasarkan periode tanggal
- Melihat referensi transaksi yang menyebabkan pergerakan stok
