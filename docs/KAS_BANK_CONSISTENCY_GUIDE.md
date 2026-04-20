# Panduan Konsistensi Data Laporan Kas dan Bank

## Overview

Dokumen ini mendefinisikan standar dan prosedur untuk memastikan konsistensi data dalam laporan Kas dan Bank, mencegah duplikasi transaksi antara sistem lama (JurnalUmum) dan sistem baru (JournalLine + JournalEntry).

## Masalah Utama

### 1. Duplikasi Transaksi
- **Penyebab**: Transaksi yang sama tersimpan di kedua sistem
- **Dampak**: Nominal total menjadi 2x lipat
- **Solusi**: Deteksi dan hapus duplikat saat pengambilan data

### 2. Format Referensi Tidak Konsisten
- **Sistem Baru**: `ref_type = 'sale'`, `ref_id = 1,2,3`
- **Sistem Lama**: `tipe_referensi = 'sale'`, `referensi = 'sale#1', 'sale#2', 'sale#3'`
- **Solusi**: Normalisasi format saat deteksi duplikat

### 3. Format Tanggal dan Nominal
- **Tanggal**: `2026-04-23 00:00:00` (standar)
- **Nominal**: `1393050.00` (2 desimal)
- **Solusi**: Gunakan format standar untuk duplicate key

## Standar Implementasi

### 1. Deteksi Duplikat

```php
// Format standar duplicate key
$duplicateKey = "Y-m-d 00:00:00_referensi_nominal.00";
// Contoh: "2026-04-23 00:00:00_sale#3_1393050.00"
```

### 2. Rule Matching

#### Ref Type Matching
- `purchase` vs `pembelian` = MATCH
- `sale` vs `sale` = MATCH
- `sale` vs `penjualan` = MATCH
- `expense_payment` vs `pembayaran_beban` = MATCH
- `penggajian` vs `penggajian` = MATCH

#### Reference ID Matching
- Format `sale#1` dengan `ref_id = 1` = MATCH
- Format `SJ-20260423-001` dengan `ref_id = 3` = MATCH

### 3. Priority System
- **Priority 1**: Sistem baru (JournalLine) - dipertahankan
- **Priority 2**: Sistem lama (JurnalUmum) - dihapus jika duplikat

## Implementasi di Controller

### getTransaksiMasuk() / getTransaksiKeluar()
```php
// 1. Get raw data dari kedua sistem
$transaksiBaruWithRaw = JournalLine::join('journal_entries', ...);
$transaksiLamaWithRaw = JurnalUmum::where(...);

// 2. Deteksi duplikat
$duplicatesToRemove = collect();
foreach ($transaksiBaruWithRaw as $jl) {
    foreach ($transaksiLamaWithRaw as $ju) {
        if (isDuplicateTransaction($jl, $ju)) {
            $duplicateKey = generateDuplicateKey($ju->tanggal, $ju->referensi, $ju->debit);
            $duplicatesToRemove->push($duplicateKey);
        }
    }
}

// 3. Filter duplikat
$uniqueTransactions = $allTransactions->filter(function($transaction) use ($duplicatesToRemove) {
    if ($transaction['source'] === 'jurnal_umum') {
        $duplicateKey = generateDuplicateKey($transaction['tanggal'], $transaction['nomor_transaksi'], $transaction['nominal']);
        return !$duplicatesToRemove->contains($duplicateKey);
    }
    return true; // Keep all new system transactions
});
```

### getDetailMasuk() / getDetailKeluar()
```php
// Gunakan logic yang sama dengan getTransaksiMasuk()
// Pastikan format duplicate key konsisten
```

## Validasi Otomatis

### 1. Consistency Service
- `KasBankConsistencyService::validateConsistency($startDate, $endDate)`
- `KasBankConsistencyService::logConsistencyCheck($startDate, $endDate)`

### 2. Types of Validation
- **Duplicate Detection**: Temukan transaksi duplikat
- **Unreferenced Transactions**: Transaksi tanpa referensi jelas
- **Inconsistent Amounts**: Transaksi dengan nominal 0 atau tidak valid

### 3. Logging
```php
// Log success
Log::info('Kas Bank consistency check passed', [
    'start_date' => $startDate,
    'end_date' => $endDate,
    'status' => 'OK'
]);

// Log issues
Log::warning('Kas Bank consistency issues found', [
    'start_date' => $startDate,
    'end_date' => $endDate,
    'issues_count' => count($issues),
    'issues' => $issues
]);
```

## Best Practices

### 1. Saat Membuat Transaksi Baru
- Selalu gunakan sistem baru (JournalLine + JournalEntry)
- Set ref_type dan ref_id dengan benar
- Hindari membuat transaksi duplikat di sistem lama

### 2. Saat Mengambil Data Laporan
- Gunakan method yang sudah ada deduplication logic
- Validasi konsistensi data secara berkala
- Log semua anomali yang ditemukan

### 3. Saat Debugging
- Cek duplicate key format
- Verifikasi ref_type dan tipe_referensi matching
- Pastikan nominal format konsisten

## Testing

### 1. Unit Test
```php
// Test duplicate detection
$this->assertTrue(isDuplicateTransaction($jl, $ju));

// Test key generation
$key = KasBankConsistencyService::generateDuplicateKey('2026-04-23', 'sale#3', 1393050);
$this->assertEquals('2026-04-23 00:00:00_sale#3_1393050.00', $key);
```

### 2. Integration Test
- Test full laporan generation
- Verify total amounts
- Check no duplicates in detail transactions

## Monitoring

### 1. Scheduled Checks
- Run consistency check daily/weekly
- Alert jika ada issues found
- Track trend duplikasi over time

### 2. Metrics to Monitor
- Number of duplicates found
- Unreferenced transactions count
- Inconsistent amount transactions
- Processing time impact

## Troubleshooting

### Common Issues
1. **Duplicate key mismatch**: Check date format and nominal precision
2. **Ref type not matching**: Verify mapping logic
3. **Wrong reference extraction**: Check regex patterns

### Debug Steps
1. Enable detailed logging
2. Check raw data from both systems
3. Verify duplicate key generation
4. Test matching logic manually

## Future Improvements

### 1. Migration Strategy
- Gradually migrate all data to new system
- Phase out old system completely
- Remove deduplication logic after migration

### 2. Prevention
- Add validation at transaction creation
- Prevent duplicate entries at database level
- Implement unique constraints

### 3. Performance
- Optimize duplicate detection queries
- Add caching for frequently accessed data
- Consider background processing for large datasets
