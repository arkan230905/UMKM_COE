# Logika Jurnal Umum Pembelian

## Overview
Sistem jurnal pembelian yang komprehensif dengan dukungan untuk berbagai jenis bahan, PPN, biaya kirim, dan metode pembayaran.

## Struktur Jurnal Pembelian

### 1. DEBIT - Persediaan Bahan
**Berdasarkan jenis bahan dan COA spesifik:**

#### Bahan Baku
- **Prioritas 1**: COA spesifik dari `bahan_bakus.coa_persediaan_id`
- **Fallback**: Kode COA `1104` - Persediaan Bahan Baku
- **Memo**: "Persediaan Bahan Baku - [Nama Bahan]"

#### Bahan Pendukung
- **Prioritas 1**: COA spesifik dari `bahan_pendukungs.coa_persediaan_id`
- **Fallback**: Kode COA `1107` - Persediaan Bahan Pendukung
- **Memo**: "Persediaan Bahan Pendukung - [Nama Bahan]"

### 2. DEBIT - PPN Masukan (Jika Ada)
- **COA**: `1130` - PPN Masukan
- **Kondisi**: `ppn_nominal > 0`
- **Memo**: "PPN Masukan [X]%"

### 3. DEBIT - Biaya Kirim (Jika Ada)
- **COA**: `5111` - Biaya Angkut Pembelian
- **Kondisi**: `biaya_kirim > 0`
- **Memo**: "Biaya kirim pembelian"

### 4. CREDIT - Pembayaran
**Berdasarkan metode pembayaran:**

#### Cash (Tunai)
- **Prioritas 1**: COA spesifik dari `pembelians.bank_id`
- **Fallback**: Kode COA `1120` - Kas
- **Memo**: "Pembayaran tunai [via Bank Name]"

#### Transfer
- **Prioritas 1**: COA spesifik dari `pembelians.bank_id`
- **Fallback**: Kode COA `1121` - Kas di Bank
- **Memo**: "Pembayaran transfer [via Bank Name]"

#### Credit (Kredit)
- **COA**: `2110` - Utang Usaha
- **Memo**: "Pembelian kredit"

## Contoh Jurnal

### Contoh 1: Pembelian Bahan Baku Tunai dengan PPN
```
Tanggal: 28/03/2026
Keterangan: Pembelian #PB-20260328-0001 - PT Supplier ABC

Persediaan Ayam Kampung (12112)     Rp 1.600.000,00
PPN Masukan (1130)                  Rp   176.000,00
    Kas (1120)                                      Rp 1.776.000,00
```

### Contoh 2: Pembelian Bahan Pendukung Transfer
```
Tanggal: 28/03/2026
Keterangan: Pembelian #PB-20260328-0002 - CV Bumbu Nusantara

Persediaan Bahan Pendukung (1107)   Rp   500.000,00
Biaya Angkut Pembelian (5111)       Rp    25.000,00
    Bank BCA (1121001)                              Rp   525.000,00
```

### Contoh 3: Pembelian Kredit dengan PPN
```
Tanggal: 28/03/2026
Keterangan: Pembelian #PB-20260328-0003 - PT Daging Segar

Persediaan Daging Sapi (12113)      Rp 2.000.000,00
PPN Masukan (1130)                  Rp   220.000,00
    Utang Usaha (2110)                              Rp 2.220.000,00
```

## Implementasi

### 1. Service Class
```php
// app/Services/PembelianJournalService.php
$journalService = new PembelianJournalService();
$journal = $journalService->createJournalFromPembelian($pembelian);
```

### 2. Observer (Otomatis)
```php
// app/Observers/PembelianJournalObserver.php
// Otomatis membuat jurnal saat pembelian dibuat/diupdate
```

### 3. Command Artisan
```bash
# Generate jurnal untuk pembelian tertentu
php artisan pembelian:generate-journal --id=123

# Generate jurnal untuk periode tertentu
php artisan pembelian:generate-journal --from=2026-03-01 --to=2026-03-31

# Preview jurnal tanpa membuat
php artisan pembelian:generate-journal --id=123 --dry-run

# Force regenerate jurnal yang sudah ada
php artisan pembelian:generate-journal --id=123 --force
```

## Mapping COA

### Persediaan
| Jenis | Kode Default | Nama Akun |
|-------|-------------|-----------|
| Bahan Baku | 1104 | Persediaan Bahan Baku |
| Bahan Pendukung | 1107 | Persediaan Bahan Pendukung |

### PPN dan Biaya
| Jenis | Kode | Nama Akun |
|-------|------|-----------|
| PPN Masukan | 1130 | PPN Masukan |
| Biaya Kirim | 5111 | Biaya Angkut Pembelian |

### Pembayaran
| Metode | Kode Default | Nama Akun |
|--------|-------------|-----------|
| Cash | 1120 | Kas |
| Transfer | 1121 | Kas di Bank |
| Credit | 2110 | Utang Usaha |

## Fitur Khusus

### 1. COA Spesifik per Item
- Setiap bahan baku/pendukung bisa memiliki COA persediaan sendiri
- Sistem otomatis menggunakan COA spesifik jika tersedia
- Fallback ke COA default jika tidak ada COA spesifik

### 2. Bank Account Spesifik
- Pembelian cash/transfer bisa menggunakan akun bank tertentu
- Diambil dari field `pembelians.bank_id`
- Fallback ke Kas/Kas di Bank jika tidak ada

### 3. Validasi Otomatis
- Validasi keseimbangan debit-kredit
- Log detail untuk tracking
- Error handling yang robust

### 4. Regenerasi Jurnal
- Hapus jurnal lama saat regenerasi
- Prevent duplicate journal entries
- Support untuk update pembelian

## Integrasi dengan Sistem

### 1. Model Relationships
```php
// Pembelian
$pembelian->details->bahanBaku->coa_persediaan_id
$pembelian->details->bahanPendukung->coa_persediaan_id
$pembelian->kasBank // COA untuk pembayaran
```

### 2. Event Handling
- Observer otomatis membuat jurnal saat pembelian created/updated
- Hapus jurnal saat pembelian deleted
- Dispatch after response untuk performa

### 3. Logging
- Log setiap pembuatan jurnal
- Track COA yang digunakan
- Error logging untuk debugging

## Maintenance

### 1. Regenerasi Jurnal
```bash
# Regenerasi semua jurnal pembelian
php artisan pembelian:generate-journal --force

# Regenerasi jurnal bulan ini
php artisan pembelian:generate-journal --from=2026-03-01 --to=2026-03-31 --force
```

### 2. Validasi Jurnal
```bash
# Preview jurnal tanpa membuat
php artisan pembelian:generate-journal --dry-run
```

### 3. Monitoring
- Check log Laravel untuk error
- Validasi balance debit-kredit
- Monitor COA mapping

## Kesimpulan

Sistem jurnal pembelian ini memberikan:
✅ **Fleksibilitas** - COA spesifik per item dan bank
✅ **Akurasi** - Handling PPN, biaya kirim, metode pembayaran
✅ **Otomatisasi** - Observer dan command untuk maintenance
✅ **Traceability** - Logging dan error handling
✅ **Konsistensi** - Validasi balance dan duplicate prevention