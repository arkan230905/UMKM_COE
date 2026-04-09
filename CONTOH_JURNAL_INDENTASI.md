# Format Jurnal dengan Indentasi

## Contoh Jurnal Pembelian dengan Format Tradisional

### Pembelian: PB-20260409-0001
**Vendor**: Tel-Mart  
**Tanggal**: 09/04/2026  

| Keterangan | Ref | Debit | Credit |
|------------|-----|-------|--------|
| Pers. Bahan Baku ayam potong | 1141 | Rp 1.600.000 | - |
| PPN Masukan | 127 | Rp 176.000 | - |
| &nbsp;&nbsp;&nbsp;&nbsp;Kas | 112 | - | Rp 1.776.000 |

## Format Text Tradisional

```
Pers. Bahan Baku ayam potong    Rp 1.600.000
PPN Masukan                     Rp   176.000
    Kas                                     Rp 1.776.000
```

## Implementasi di Database

Jurnal lines disimpan dengan memo yang sudah ter-indentasi:
- **Debit**: `"Pers. Bahan Baku ayam potong"`
- **Debit**: `"PPN Masukan"`
- **Credit**: `"    Kas"` (dengan 4 spasi di depan)

## Contoh Skenario Lain

### Pembelian Bahan Pendukung Transfer
```
Pers. Bahan Pendukung Air       Rp   500.000
PPN Masukan                     Rp    55.000
    Kas Bank                                Rp   555.000
```

### Pembelian Kredit dengan Biaya Kirim
```
Pers. Bahan Baku ayam potong    Rp 2.000.000
PPN Masukan                     Rp   220.000
Biaya Kirim                     Rp    50.000
    Utang Usaha                             Rp 2.270.000
```

### Pembelian Multiple Items
```
Pers. Bahan Baku ayam potong    Rp 1.000.000
Pers. Bahan Baku ayam kampung   Rp   800.000
Pers. Bahan Pendukung Air       Rp   200.000
PPN Masukan                     Rp   220.000
    Kas                                     Rp 2.220.000
```

## Fitur Format Indentasi

✅ **Akun Debit**: Rata kiri (normal)  
✅ **Akun Credit**: Menjorok 4 spasi ke kanan  
✅ **Konsisten**: Semua akun kredit ter-indentasi  
✅ **Readable**: Format mudah dibaca dan dipahami  

## View Template

Tersedia view template di `resources/views/jurnal/format-tradisional.blade.php` untuk menampilkan jurnal dengan format indentasi yang proper di web interface.

## Kesimpulan

Format jurnal sekarang mengikuti standar akuntansi tradisional dimana:
- Akun yang di-debit ditulis rata kiri
- Akun yang di-kredit ditulis menjorok ke kanan (indented)
- Format ini memudahkan pembacaan dan pemahaman jurnal