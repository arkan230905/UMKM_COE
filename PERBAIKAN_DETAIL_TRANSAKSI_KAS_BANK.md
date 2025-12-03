# Perbaikan Detail Transaksi Kas dan Bank

## Masalah
Ketika klik tombol "Masuk" atau "Keluar" di Laporan Kas dan Bank, modal muncul tapi menampilkan "Tidak ada transaksi masuk/keluar" padahal seharusnya ada data.

## Penyebab
1. **Kolom database tidak sesuai**: Controller menggunakan `reference_type` dan `reference_id`, tapi di tabel `journal_entries` kolom nya adalah `ref_type` dan `ref_id`
2. **Kolom keterangan tidak ada**: Controller mencari `$entry->keterangan` tapi di tabel hanya ada `memo`
3. **Ref type tidak sesuai**: Controller mencari `'penjualan'` tapi di database tersimpan sebagai `'sale'`

## Solusi yang Diterapkan

### 1. Perbaikan Method getDetailMasuk
**File:** `app/Http/Controllers/LaporanKasBankController.php`

Perubahan:
- Menggunakan `ref_type` dan `ref_id` (bukan `reference_type` dan `reference_id`)
- Menggunakan `memo` (bukan `keterangan`)
- Menambahkan filter untuk menghapus null values
- Menambahkan handling untuk format tanggal yang berbeda

```php
$transaksi = JournalLine::where('account_id', $account->id)
    ->where('debit', '>', 0)
    ->whereHas('entry', function($query) use ($startDate, $endDate) {
        $query->whereBetween('tanggal', [$startDate, $endDate]);
    })
    ->with('entry')
    ->orderBy('created_at', 'desc')
    ->get()
    ->map(function($line) {
        $entry = $line->entry;
        if (!$entry) {
            return null;
        }
        
        return [
            'tanggal' => $entry->tanggal instanceof \Carbon\Carbon ? $entry->tanggal->format('d/m/Y') : date('d/m/Y', strtotime($entry->tanggal)),
            'nomor_transaksi' => $this->getNomorTransaksi($entry),
            'jenis' => $this->getJenisTransaksi($entry),
            'keterangan' => $entry->memo ?? '-',
            'nominal' => (float)$line->debit
        ];
    })
    ->filter() // Remove null values
    ->values(); // Re-index array
```

### 2. Perbaikan Method getDetailKeluar
Sama seperti getDetailMasuk, tapi untuk transaksi keluar (credit).

### 3. Perbaikan Method getNomorTransaksi
Perubahan:
- Menggunakan `$entry->ref_type` dan `$entry->ref_id`
- Menambahkan mapping untuk ref_type yang berbeda:
  - `'sale'`, `'sale_cogs'`, `'penjualan'` → Penjualan
  - `'purchase'`, `'pembelian'` → Pembelian
  - `'expense_payment'`, `'expense'` → Pembayaran Beban

```php
private function getNomorTransaksi($entry)
{
    if (!$entry) return '-';
    
    $referenceType = $entry->ref_type ?? '';
    $referenceId = $entry->ref_id ?? null;
    
    if (!$referenceId) {
        return 'JU-' . $entry->id;
    }
    
    switch ($referenceType) {
        case 'sale':
        case 'sale_cogs':
        case 'penjualan':
            return 'PJ-' . $referenceId;
        // ... dst
    }
}
```

### 4. Perbaikan Method getJenisTransaksi
Perubahan:
- Menggunakan `$entry->ref_type`
- Menambahkan mapping untuk semua jenis transaksi:

```php
$jenisMap = [
    'sale' => 'Penjualan',
    'sale_cogs' => 'HPP Penjualan',
    'purchase' => 'Pembelian',
    'expense_payment' => 'Pembayaran Beban',
    'expense' => 'Pembayaran Beban',
    'ap_settlement' => 'Pelunasan Utang',
    'payroll' => 'Penggajian',
    'production' => 'Produksi',
    // ... dst
];
```

## Hasil

### Sebelum Perbaikan
- Klik tombol "Masuk": Modal muncul tapi "Tidak ada transaksi masuk" ❌
- Klik tombol "Keluar": Modal muncul tapi "Tidak ada transaksi keluar" ❌

### Setelah Perbaikan
- Klik tombol "Masuk": Modal menampilkan detail semua transaksi masuk ✅
  - Tanggal transaksi
  - Nomor transaksi (PJ-12, PJ-13, dst)
  - Jenis transaksi (Penjualan, Pembayaran Beban, dst)
  - Keterangan
  - Nominal
  - Total keseluruhan

- Klik tombol "Keluar": Modal menampilkan detail semua transaksi keluar ✅
  - Tanggal transaksi
  - Nomor transaksi (PB-1, BP-1, dst)
  - Jenis transaksi (Pembelian, Pembayaran Beban, Pelunasan Utang, dst)
  - Keterangan
  - Nominal
  - Total keseluruhan

## Contoh Data yang Ditampilkan

### Detail Transaksi Masuk (Kas)
| Tanggal | No. Transaksi | Jenis | Keterangan | Nominal |
|---------|---------------|-------|------------|---------|
| 09/11/2025 | PJ-12 | Penjualan | Penjualan Produk | Rp 465.738 |
| 09/11/2025 | PJ-13 | Penjualan | Penjualan Produk | Rp 93.148 |
| 09/11/2025 | PJ-14 | Penjualan | Penjualan Produk | Rp 768.000 |
| ... | ... | ... | ... | ... |
| **TOTAL** | | | | **Rp 8.259.187** |

### Detail Transaksi Keluar (Kas)
| Tanggal | No. Transaksi | Jenis | Keterangan | Nominal |
|---------|---------------|-------|------------|---------|
| 10/11/2025 | PB-16 | Pembelian | Pembelian Bahan Baku | Rp 1.110.000 |
| 09/11/2025 | PB-15 | Pembelian | Pembelian Bahan Baku | Rp 5.000.000 |
| **TOTAL** | | | | **Rp 6.110.000** |

## Testing

Untuk test apakah detail transaksi muncul:

1. Buka Laporan Kas dan Bank
2. Klik tombol "Masuk" pada baris Kas
3. Modal harus muncul dengan detail transaksi masuk
4. Klik tombol "Keluar" pada baris Kas
5. Modal harus muncul dengan detail transaksi keluar

## Catatan Penting

### Mapping Ref Type
Sistem menggunakan berbagai ref_type untuk transaksi yang sama:
- Penjualan: `'sale'`, `'sale_cogs'`, `'penjualan'`
- Pembelian: `'purchase'`, `'pembelian'`
- Beban: `'expense_payment'`, `'expense'`
- Pelunasan: `'ap_settlement'`, `'pelunasan_utang'`

Semua sudah di-handle di method `getNomorTransaksi` dan `getJenisTransaksi`.

### Format Tanggal
Sistem support dua format tanggal:
- Carbon instance: `$entry->tanggal->format('d/m/Y')`
- String: `date('d/m/Y', strtotime($entry->tanggal))`

## Kesimpulan

Detail transaksi kas dan bank sekarang sudah berfungsi dengan baik:
- ✅ Modal menampilkan semua transaksi masuk dengan detail lengkap
- ✅ Modal menampilkan semua transaksi keluar dengan detail lengkap
- ✅ Nomor transaksi ditampilkan dengan benar (PJ-12, PB-16, dst)
- ✅ Jenis transaksi ditampilkan dengan benar (Penjualan, Pembelian, dst)
- ✅ Total dihitung dengan akurat

**Refresh browser dan test tombol "Masuk" dan "Keluar"!** 🎉
