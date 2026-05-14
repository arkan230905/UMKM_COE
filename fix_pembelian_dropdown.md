# Perbaikan Bug Dropdown Nama Item - Transaksi Pembelian

## Masalah yang Ditemukan

1. **Event listener tidak terpasang dengan benar** - Menggunakan inline `onchange` yang conflict dengan event listener di DOMContentLoaded
2. **Fungsi JavaScript terduplikasi** - Ada wrapping function yang menyebabkan scope issue
3. **Dropdown tetap disabled** - JavaScript tidak berhasil mengubah state disabled menjadi enabled

## Penyebab Root Cause

1. Inline `onchange="updateItemsBasedOnVendor(this)"` di vendor select tidak reliable
2. Fungsi `updateSubSatuanInfo` didefinisikan di dalam DOMContentLoaded sehingga tidak accessible dari fungsi lain
3. Browser cache menyimpan versi lama JavaScript

## Solusi yang Diterapkan

### 1. Mengubah Event Handler dari Inline ke Event Listener
### 2. Memindahkan Fungsi ke Global Scope
### 3. Menambahkan Debugging Lengkap
### 4. Clear Cache

## File yang Diubah

- `resources/views/transaksi/pembelian/create.blade.php`

## Testing

1. Hard refresh (Ctrl + Shift + R)
2. Buka Console (F12)
3. Pilih vendor
4. Lihat log dan dropdown harus aktif
