# Sistem HPP yang Benar

## Prinsip Dasar
1. **Laporan Stok** = Sumber kebenaran (JANGAN DIUBAH)
2. **BOM Tables** = Mengikuti hasil akhir laporan stok
3. **Saldo Awal** = Data historis (TIDAK BOLEH DIUBAH)

## Alur yang Benar

### 1. Data Flow
```
Transaksi Pembelian → Stock Movements → Laporan Stok (Perhitungan Logika) → Update BOM
```

### 2. Laporan Stok (Sumber Kebenaran)
- Menggunakan perhitungan logika dari `stock_movements`
- Menghitung harga rata-rata tertimbang
- Menghasilkan harga final yang akurat
- **TIDAK BOLEH DIUBAH** oleh sistem otomatis

### 3. Update BOM (Mengikuti Laporan Stok)
- Mengambil harga dari hasil akhir laporan stok
- Update `bom_job_bahan_pendukung` dan `bom_job_bbb`
- Konversi satuan sesuai resep
- Recalculate subtotal

## Command yang Benar

### Manual Update
```bash
# Update semua BOM berdasarkan laporan stok
php artisan bom:update-from-stock-report

# Update item spesifik
php artisan bom:update-from-stock-report --item-type=support --item-id=14

# Dry run untuk testing
php artisan bom:update-from-stock-report --dry-run
```

### Scheduled Update (Otomatis)
```bash
php artisan bom:scheduled-update
```

## Hasil yang Dicapai

### Sebelum (Salah)
- BOM menggunakan harga pembelian terakhir
- Tidak mempertimbangkan rata-rata tertimbang
- Saldo awal diubah-ubah

### Sesudah (Benar)
- BOM menggunakan harga dari hasil akhir laporan stok
- Mempertimbangkan semua transaksi dan rata-rata tertimbang
- Saldo awal tetap sebagai data historis
- HPP akurat untuk produksi

## Contoh Hasil Update

### Minyak Goreng
- **Laporan Stok Final**: Rp 18.411,76/liter
- **BOM Update**: Rp 18,41/ml (konversi 1 liter = 1000 ml)
- **Subtotal**: 500ml × Rp 18,41 = Rp 9.205,88

### Kemasan
- **Laporan Stok Final**: Rp 4.666,67/pieces
- **BOM Update**: Rp 4.666,67/pieces
- **Subtotal**: 1 pieces × Rp 4.666,67 = Rp 4.666,67

## Manfaat untuk Bisnis

1. **HPP Akurat**: Menggunakan harga rata-rata tertimbang dari semua transaksi
2. **Data Historis Terjaga**: Saldo awal tidak berubah
3. **Otomatis**: BOM selalu update sesuai kondisi stok terkini
4. **Mencegah Kerugian**: Harga produksi selalu mengikuti cost sebenarnya
5. **Audit Trail**: Semua perubahan tercatat dan dapat dilacak

## Peringatan

❌ **JANGAN PERNAH**:
- Mengubah saldo awal di laporan stok
- Update BOM berdasarkan stock movement mentah
- Mengabaikan konversi satuan

✅ **SELALU**:
- Gunakan hasil akhir laporan stok sebagai acuan
- Jalankan update BOM setelah ada transaksi besar
- Verifikasi hasil update sebelum produksi