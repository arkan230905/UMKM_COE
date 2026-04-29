# Perbaikan Posting Penyusutan Aset ke Jurnal Umum dan Buku Besar

## Tanggal: 29 April 2026

## Masalah yang Diperbaiki

Sebelumnya, sistem posting penyusutan aset memiliki masalah:

1. **Menggunakan tabel lama (`jurnal_umum`)** - Tidak konsisten dengan sistem jurnal modern yang menggunakan `journal_entries` + `journal_lines`
2. **Tidak terintegrasi dengan Buku Besar** - Laporan Buku Besar dan Neraca Saldo tidak membaca dari tabel `jurnal_umum`
3. **Pengecekan duplikasi tidak akurat** - Menggunakan LIKE pattern pada keterangan, bisa menyebabkan false positive/negative
4. **Tidak mengikuti siklus akuntansi yang benar** - Seharusnya: Posting → Jurnal Umum → Buku Besar → Neraca Saldo

## Solusi yang Diimplementasikan

### 1. Update Method `postIndividualDepreciation` di AsetController

**File**: `app/Http/Controllers/AsetController.php`

**Perubahan**:

#### A. Pengecekan Duplikasi yang Lebih Akurat
```php
// SEBELUM: Menggunakan LIKE pattern (tidak akurat)
$existingEntry = \App\Models\JurnalUmum::where('tanggal', now()->endOfMonth()->format('Y-m-d'))
    ->where('keterangan', 'LIKE', "%{$aset->nama_aset}%")
    ->where('keterangan', 'LIKE', '%Penyusutan%')
    ->first();

// SESUDAH: Menggunakan ref_type dan ref_id (akurat)
$existingEntry = \App\Models\JournalEntry::where('ref_type', 'depreciation')
    ->where('ref_id', $aset->id)
    ->whereYear('tanggal', now()->year)
    ->whereMonth('tanggal', now()->month)
    ->first();
```

#### B. Pembuatan Jurnal Entry yang Benar
```php
// SEBELUM: Langsung ke JurnalUmum (2 record terpisah)
\App\Models\JurnalUmum::create([...]);  // Debit
\App\Models\JurnalUmum::create([...]);  // Kredit

// SESUDAH: Menggunakan JournalEntry + JournalLine (struktur proper)
// 1. Buat header jurnal
$journalEntry = \App\Models\JournalEntry::create([
    'tanggal' => $currentDate,
    'ref_type' => 'depreciation',
    'ref_id' => $aset->id,
    'memo' => $memo,
]);

// 2. Buat line debit (Beban Penyusutan)
\App\Models\JournalLine::create([
    'journal_entry_id' => $journalEntry->id,
    'coa_id' => $aset->expense_coa_id,
    'debit' => $monthlyDepreciation,
    'credit' => 0,
    'memo' => "Beban Penyusutan - {$aset->nama_aset}",
]);

// 3. Buat line kredit (Akumulasi Penyusutan)
\App\Models\JournalLine::create([
    'journal_entry_id' => $journalEntry->id,
    'coa_id' => $aset->accum_depr_coa_id,
    'debit' => 0,
    'credit' => $monthlyDepreciation,
    'memo' => "Akumulasi Penyusutan - {$aset->nama_aset}",
]);
```

### 2. Update Pengecekan Status Posting

**File**: `app/Http/Controllers/AsetController.php`

**Method yang Diupdate**:
- `index()` - Untuk badge "Sudah Posting" / "Belum Posting" di tabel
- `show()` - Untuk status posting di halaman detail
- `edit()` - Untuk lock form jika sudah diposting

**Perubahan**:
```php
// SEBELUM: Cek di JurnalUmum dengan ref_type 'depr'
$sudahDiposting = JournalEntry::where('ref_type', 'depr')
    ->where('ref_id', $aset->id)
    ->exists();

// SESUDAH: Cek di JournalEntry dengan ref_type 'depreciation'
$sudahDiposting = JournalEntry::where('ref_type', 'depreciation')
    ->where('ref_id', $aset->id)
    ->exists();
```

**Untuk index method**:
```php
// SEBELUM: Cek di JurnalUmum dengan LIKE pattern
$aset->is_posted_this_month = \App\Models\JurnalUmum::where('tanggal', now()->endOfMonth()->format('Y-m-d'))
    ->where('keterangan', 'LIKE', "%{$aset->nama_aset}%")
    ->where('keterangan', 'LIKE', '%Penyusutan%')
    ->exists();

// SESUDAH: Cek di JournalEntry dengan ref_type dan periode
$aset->is_posted_this_month = \App\Models\JournalEntry::where('ref_type', 'depreciation')
    ->where('ref_id', $aset->id)
    ->whereYear('tanggal', now()->year)
    ->whereMonth('tanggal', now()->month)
    ->exists();
```

## Alur Sistem Setelah Perbaikan

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. User klik tombol "Posting" di halaman Aset                  │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│ 2. AsetController::postIndividualDepreciation()                 │
│    - Validasi COA lengkap                                       │
│    - Cek duplikasi (ref_type='depreciation', ref_id=aset_id)   │
│    - Hitung penyusutan bulan ini                                │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│ 3. Buat JournalEntry (Header)                                   │
│    - tanggal: akhir bulan                                       │
│    - ref_type: 'depreciation'                                   │
│    - ref_id: ID aset                                            │
│    - memo: "Penyusutan Aset [nama] - [periode]"                │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│ 4. Buat JournalLine (Detail)                                    │
│    Line 1 (Debit):                                              │
│    - coa_id: expense_coa_id (Beban Penyusutan)                  │
│    - debit: nilai penyusutan                                    │
│    - credit: 0                                                  │
│                                                                 │
│    Line 2 (Kredit):                                             │
│    - coa_id: accum_depr_coa_id (Akumulasi Penyusutan)          │
│    - debit: 0                                                   │
│    - credit: nilai penyusutan                                   │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│ 5. Data Otomatis Terbaca di Laporan                            │
│    ✓ Jurnal Umum (journal_entries + journal_lines)             │
│    ✓ Buku Besar (via TrialBalanceService::getMutasiPeriode)    │
│    ✓ Neraca Saldo (via TrialBalanceService)                    │
│    ✓ Laporan Keuangan (Laba Rugi, Neraca)                      │
└─────────────────────────────────────────────────────────────────┘
```

## Integrasi dengan Sistem Laporan

### Buku Besar
**Service**: `app/Services/TrialBalanceService.php`
**Method**: `getMutasiPeriode()`

```php
$mutasi = JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('coas.kode_akun', $coa->kode_akun)
    ->whereBetween('journal_entries.tanggal', [$startDate, $endDate])
    ->selectRaw('
        COALESCE(SUM(journal_lines.debit), 0) as total_debit,
        COALESCE(SUM(journal_lines.credit), 0) as total_kredit
    ')
    ->first();
```

**Hasil**: Semua entry di `journal_lines` (termasuk penyusutan aset) otomatis terbaca di Buku Besar.

### Neraca Saldo
**Service**: `app/Services/TrialBalanceService.php`
**Method**: `calculateTrialBalance()`

Neraca Saldo menghitung saldo akhir setiap akun dengan formula:
- **Akun Normal Debit** (Aset, Beban): `Saldo Akhir = Saldo Awal + Total Debit - Total Kredit`
- **Akun Normal Kredit** (Kewajiban, Modal, Pendapatan): `Saldo Akhir = Saldo Awal - Total Debit + Total Kredit`

Data mutasi diambil dari `getMutasiPeriode()` yang membaca `journal_lines`.

## Contoh Jurnal Entry yang Dibuat

### Skenario
- **Aset**: Komputer Server
- **Harga Perolehan**: Rp 10,000,000
- **Metode**: Garis Lurus
- **Umur Manfaat**: 5 tahun
- **Penyusutan/bulan**: Rp 166,667
- **Periode**: April 2026

### Journal Entry yang Dibuat

**Tabel: journal_entries**
| id | tanggal    | ref_type     | ref_id | memo                                              |
|----|------------|--------------|--------|---------------------------------------------------|
| 1  | 2026-04-30 | depreciation | 5      | Penyusutan Aset Komputer Server (garis_lurus) - 2026-04 |

**Tabel: journal_lines**
| id | journal_entry_id | coa_id | debit   | credit  | memo                                    |
|----|------------------|--------|---------|---------|----------------------------------------|
| 1  | 1                | 45     | 166667  | 0       | Beban Penyusutan - Komputer Server     |
| 2  | 1                | 23     | 0       | 166667  | Akumulasi Penyusutan - Komputer Server |

**COA yang Terpengaruh**:
- **COA 45**: Beban Penyusutan (Expense) - Debit bertambah Rp 166,667
- **COA 23**: Akumulasi Penyusutan (Contra Asset) - Kredit bertambah Rp 166,667

## Pencegahan Double Posting

Sistem mencegah double posting dengan cara:

1. **Pengecekan sebelum posting**:
   ```php
   $existingEntry = \App\Models\JournalEntry::where('ref_type', 'depreciation')
       ->where('ref_id', $aset->id)
       ->whereYear('tanggal', now()->year)
       ->whereMonth('tanggal', now()->month)
       ->first();
   ```

2. **Jika sudah ada entry untuk aset ini di bulan ini**:
   - Return error: "Penyusutan untuk aset ini sudah diposting bulan ini"
   - Tombol "Posting" tidak akan berfungsi

3. **Badge status di halaman index**:
   - ✅ **Sudah Posting** (hijau) - Jika ada entry bulan ini
   - ⏰ **Belum Posting** (kuning) - Jika belum ada entry bulan ini

## Testing Checklist

- [x] Posting penyusutan aset berhasil membuat journal entry
- [x] Journal entry memiliki 2 lines (debit & kredit)
- [x] Nominal debit = kredit (balanced)
- [x] Entry muncul di laporan Jurnal Umum
- [x] Entry muncul di Buku Besar untuk kedua akun
- [x] Saldo di Neraca Saldo terpengaruh dengan benar
- [x] Tidak bisa posting 2x untuk aset yang sama di bulan yang sama
- [x] Badge status posting di index page update dengan benar
- [x] Form edit terkunci jika aset sudah pernah diposting

## Files yang Dimodifikasi

1. **app/Http/Controllers/AsetController.php**
   - Method `postIndividualDepreciation()` - Update logika posting
   - Method `index()` - Update pengecekan is_posted_this_month
   - Method `show()` - Update pengecekan sudah_diposting
   - Method `edit()` - Update pengecekan sudah_diposting

## Catatan Penting

1. **Tidak ada perubahan pada tabel database** - Hanya perubahan logika aplikasi
2. **Backward compatibility** - Entry lama di `jurnal_umum` tidak terpengaruh
3. **Konsistensi dengan sistem lain** - Mengikuti pola yang sama dengan PenggajianController dan PembayaranBebanController
4. **Logging lengkap** - Setiap posting dicatat di log untuk audit trail

## Referensi

- **Model**: `app/Models/JournalEntry.php`, `app/Models/JournalLine.php`
- **Service**: `app/Services/TrialBalanceService.php`
- **Route**: `routes/web.php` - `Route::post('aset/{aset}/post-depreciation', ...)`
- **View**: `resources/views/master-data/aset/index.blade.php` (tombol posting)

---

**Dibuat oleh**: Kiro AI Assistant  
**Tanggal**: 29 April 2026  
**Status**: ✅ Selesai dan Siap Digunakan
