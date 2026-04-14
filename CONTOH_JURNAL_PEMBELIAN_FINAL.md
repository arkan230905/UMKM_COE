# Contoh Jurnal Pembelian Final

## Jurnal yang Berhasil Dibuat

### Pembelian: PB-20260409-0001
**Vendor**: Tel-Mart  
**Tanggal**: 09/04/2026  
**Total**: Rp 1.776.000

| Keterangan | Ref | Debit | Credit |
|------------|-----|-------|--------|
| Pers. Bahan Baku ayam potong | 1141 | Rp 1.600.000 | |
| PPN Masukan | 127 | Rp 176.000 | |
| Kas | 112 | | Rp 1.776.000 |

## Format Jurnal Sesuai Permintaan

```
Pers. Bahan Baku Ayam Potong    Rp 1.600.000
PPN Masukan                     Rp   176.000
    Kas                                     Rp 1.776.000
```

## Logika yang Diterapkan

### 1. **Persediaan Spesifik**
- ✅ Menggunakan COA persediaan spesifik untuk setiap bahan
- ✅ Ayam Potong → "Pers. Bahan Baku ayam potong" (1141)
- ✅ Ayam Kampung → "Pers. Bahan Baku ayam kampung" (1142)
- ✅ Bebek → "Pers. Bahan Baku bebek" (1143)

### 2. **PPN Masukan**
- ✅ COA: 127 - PPN Masukkan
- ✅ Memo: "PPN Masukan"
- ✅ Hanya muncul jika ada PPN

### 3. **Pembayaran**
- ✅ **Cash**: COA 112 - Kas
- ✅ **Transfer**: COA 111 - Kas Bank (atau bank spesifik)
- ✅ **Credit**: COA 2110 - Utang Usaha

### 4. **Biaya Kirim** (jika ada)
- ✅ COA: 5111 - Biaya Angkut Pembelian
- ✅ Memo: "Biaya Kirim"

## Contoh Skenario Lain

### Pembelian Bahan Pendukung Transfer dengan PPN
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

## Implementasi

### Service Class
```php
use App\Services\PembelianJournalService;

$service = new PembelianJournalService();
$journal = $service->createJournalFromPembelian($pembelian);
```

### Command Artisan
```bash
# Generate jurnal untuk pembelian tertentu
php artisan pembelian:generate-journal --id=4

# Preview jurnal
php artisan pembelian:generate-journal --id=4 --dry-run
```

### Observer (Otomatis)
Jurnal otomatis dibuat saat pembelian disimpan melalui `PembelianJournalObserver`.

## Validasi

✅ **Balance**: Total Debit = Total Credit  
✅ **COA Mapping**: Setiap bahan menggunakan COA persediaan spesifik  
✅ **Error Handling**: Validasi COA dan data pembelian  
✅ **Logging**: Semua operasi tercatat di log Laravel  

## Kesimpulan

Sistem jurnal pembelian sekarang menghasilkan format yang sesuai permintaan:
- Nama akun langsung (tanpa prefix "Persediaan Bahan Baku -")
- PPN Masukan sederhana
- Kas/Bank/Utang sesuai metode pembayaran
- COA spesifik untuk setiap jenis bahan