# Quick Reference - Kode Akun COA

## Kode Akun yang Sering Digunakan

### 💰 KAS DAN BANK
- **1101** - Kas Kecil (untuk transaksi tunai)
- **1102** - Kas di Bank (untuk transaksi transfer)

### 📦 PERSEDIAAN
- **1104** - Persediaan Bahan Baku
- **1105** - Persediaan Barang Dalam Proses (WIP)
- **1107** - Persediaan Barang Jadi

### 👥 PIUTANG DAN HUTANG
- **1103** - Piutang Usaha (dari penjualan kredit)
- **2101** - Hutang Usaha (dari pembelian kredit)
- **2103** - Hutang Gaji (BTKL)
- **2104** - Hutang BOP

### 💵 PENDAPATAN DAN BEBAN
- **4101** - Penjualan Produk
- **5001** - Harga Pokok Penjualan (HPP)
- **5103** - Beban Penyusutan
- **5104** - Beban Denda dan Bunga
- **5105** - Penyesuaian HPP (Diskon Pembelian)

---

## Jurnal Standar

### Pembelian Bahan Baku
```
TUNAI:
Dr 1104 (Persediaan Bahan Baku)
Cr 1101 (Kas Kecil)

TRANSFER:
Dr 1104 (Persediaan Bahan Baku)
Cr 1102 (Kas di Bank)

KREDIT:
Dr 1104 (Persediaan Bahan Baku)
Cr 2101 (Hutang Usaha)
```

### Penjualan Produk
```
TUNAI:
Dr 1101 (Kas Kecil)
Cr 4101 (Penjualan Produk)

Dr 5001 (HPP)
Cr 1107 (Persediaan Barang Jadi)

KREDIT:
Dr 1103 (Piutang Usaha)
Cr 4101 (Penjualan Produk)

Dr 5001 (HPP)
Cr 1107 (Persediaan Barang Jadi)
```

### Produksi
```
KONSUMSI BAHAN:
Dr 1105 (WIP)
Cr 1104 (Persediaan Bahan Baku)

BTKL & BOP:
Dr 1105 (WIP)
Cr 2103 (Hutang Gaji)
Cr 2104 (Hutang BOP)

SELESAI PRODUKSI:
Dr 1107 (Persediaan Barang Jadi)
Cr 1105 (WIP)
```

### Pelunasan Hutang
```
Dr 2101 (Hutang Usaha)
Cr 1101/1102 (Kas/Bank)

JIKA ADA DISKON:
Cr 5105 (Penyesuaian HPP)

JIKA ADA DENDA:
Dr 5104 (Beban Denda)
```

---

## Command Penting

```bash
# Jalankan seeder COA
php artisan db:seed --class=CompleteCoaSeeder

# Clear cache
php artisan cache:clear
php artisan config:clear
```

---

## Tips

1. **Selalu gunakan kode 4 digit** (1101, bukan 101)
2. **Kas Kecil (1101)** untuk transaksi tunai
3. **Kas di Bank (1102)** untuk transaksi transfer
4. **Hutang Usaha (2101)** untuk pembelian kredit (BUKAN Piutang!)
5. **Export Excel** sekarang berfungsi tanpa error

---

**Simpan file ini untuk referensi cepat!**
