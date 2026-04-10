# Format Jurnal Pembelian Final

## ✅ Format yang Berhasil Diimplementasi

### Jurnal Pembelian: PB-20260409-0001
**Tanggal**: 09/04/2026  
**Vendor**: Tel-Mart  
**Keterangan**: Pembelian - Tel-Mart

```
Nama Akun                                          Debit           Credit
--------------------------------------------------------------------------------
Pers. Bahan Baku ayam potong                    Rp 1.600.000         -
PPN Masukan                                      Rp   176.000         -
    Kas                                                  -     Rp 1.776.000
--------------------------------------------------------------------------------
TOTAL                                            Rp 1.776.000   Rp 1.776.000
```

## 🎯 Fitur Layout yang Diimplementasi

### 1. **Akun Debit** (Rata Kiri)
- Pers. Bahan Baku ayam potong
- PPN Masukan  
- Biaya Kirim (jika ada)

### 2. **Akun Kredit** (Menjorok 4 Spasi ke Kanan)
- `    Kas` (untuk pembayaran cash)
- `    Kas Bank` (untuk pembayaran transfer)
- `    Utang Usaha` (untuk pembelian kredit)

### 3. **Format Kolom**
- **Nama Akun**: 50 karakter (dengan indentasi untuk kredit)
- **Debit**: 15 karakter, rata kanan
- **Credit**: 15 karakter, rata kanan

## 📋 Contoh Skenario Berbagai Jenis

### Pembelian Transfer Bahan Pendukung
```
Nama Akun                                          Debit           Credit
--------------------------------------------------------------------------------
Pers. Bahan Pendukung Air                       Rp   500.000         -
PPN Masukan                                      Rp    55.000         -
    Kas Bank                                             -       Rp   555.000
--------------------------------------------------------------------------------
```

### Pembelian Kredit dengan Biaya Kirim
```
Nama Akun                                          Debit           Credit
--------------------------------------------------------------------------------
Pers. Bahan Baku ayam potong                    Rp 2.000.000         -
PPN Masukan                                      Rp   220.000         -
Biaya Kirim                                      Rp    50.000         -
    Utang Usaha                                          -     Rp 2.270.000
--------------------------------------------------------------------------------
```

### Pembelian Multiple Items
```
Nama Akun                                          Debit           Credit
--------------------------------------------------------------------------------
Pers. Bahan Baku ayam potong                    Rp 1.000.000         -
Pers. Bahan Baku ayam kampung                   Rp   800.000         -
Pers. Bahan Pendukung Air                       Rp   200.000         -
PPN Masukan                                      Rp   220.000         -
    Kas                                                  -     Rp 2.220.000
--------------------------------------------------------------------------------
```

## 🔧 Implementasi Teknis

### 1. **Database Storage**
Jurnal lines disimpan dengan memo yang sudah ter-format:
```php
// Akun debit
'memo' => 'Pers. Bahan Baku ayam potong'

// Akun kredit (dengan indentasi)
'memo' => '    Kas'
```

### 2. **Service Class**
```php
// Format memo dengan indentasi untuk akun kredit
$formattedMemo = $line['credit'] > 0 ? '    ' . $line['memo'] : $line['memo'];
```

### 3. **View Template**
```blade
@if($line->credit > 0)
    <td style="padding-left: 2rem;">{{ $line->coa->nama_akun }}</td>
@else
    <td>{{ $line->coa->nama_akun }}</td>
@endif
```

### 4. **Command Preview**
```php
// Tampilkan akun kredit dengan indentasi (4 spasi di depan)
echo sprintf("%-50s %15s %15s\n", 
    '    ' . $namaAkunKredit,  // 4 spasi untuk indentasi
    '-',
    'Rp ' . number_format($totalCredit, 0, ',', '.')
);
```

## 📊 Mapping COA yang Digunakan

### Persediaan (Debit)
- **1141** - Pers. Bahan Baku ayam potong
- **1142** - Pers. Bahan Baku ayam kampung  
- **1143** - Pers. Bahan Baku bebek
- **1150** - Pers. Bahan Pendukung Air
- **1151** - Pers. Bahan Pendukung Minyak Goreng
- dll.

### PPN dan Biaya (Debit)
- **127** - PPN Masukan
- **5111** - Biaya Kirim

### Pembayaran (Credit - Menjorok)
- **112** - Kas
- **111** - Kas Bank
- **2110** - Utang Usaha

## ✅ Hasil Akhir

Format jurnal sekarang mengikuti standar akuntansi tradisional:
- ✅ Akun debit rata kiri
- ✅ Akun kredit menjorok ke kanan (4 spasi)
- ✅ COA spesifik per bahan
- ✅ PPN Masukan otomatis
- ✅ Metode pembayaran fleksibel
- ✅ Layout yang rapi dan mudah dibaca

Format ini sesuai dengan praktik akuntansi standar dan mudah dipahami oleh akuntan maupun pengguna sistem.