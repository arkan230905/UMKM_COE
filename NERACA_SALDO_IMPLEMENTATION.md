# Implementasi Neraca Saldo Berbasis Buku Besar

## Overview

Implementasi ini memperbaiki fitur Neraca Saldo di Laravel dengan mengambil data langsung dari **Buku Besar (journal_lines)**, bukan dari input manual. Sesuai dengan prinsip akuntansi yang benar, neraca saldo adalah ringkasan saldo akhir semua akun berdasarkan transaksi yang sudah diposting.

## Struktur Database

### Tabel Utama
- **`journal_entries`**: Header jurnal (tanggal, referensi)
- **`journal_lines`**: Detail jurnal (coa_id, debit, credit)
- **`coas`**: Chart of Accounts (kode_akun, nama_akun, tipe_akun, saldo_awal)

### Relasi
```sql
journal_entries (1) -> (n) journal_lines
journal_lines (n) -> (1) coas
```

## Logika Perhitungan

### Formula Saldo Akhir
```
Akun Normal Debit (Aset, Beban):
Saldo Akhir = Saldo Awal + Total Debit - Total Kredit

Akun Normal Kredit (Kewajiban, Modal, Pendapatan):
Saldo Akhir = Saldo Awal - Total Debit + Total Kredit
```

### Normal Balance berdasarkan Kode Akun
- **1xx**: Aset (Debit Normal)
- **2xx**: Kewajiban (Kredit Normal)
- **3xx**: Modal/Ekuitas (Kredit Normal)
- **4xx**: Pendapatan (Kredit Normal)
- **5xx, 6xx**: Beban (Debit Normal)

### Mapping ke Kolom Neraca Saldo
```
Jika Saldo Akhir > 0:
  - Akun Normal Debit → Tampil di kolom Debit
  - Akun Normal Kredit → Tampil di kolom Kredit

Jika Saldo Akhir < 0 (Abnormal):
  - Akun Normal Debit → Tampil di kolom Kredit
  - Akun Normal Kredit → Tampil di kolom Debit

Jika Saldo Akhir = 0:
  - Tidak tampil di kedua kolom
```

## File yang Dibuat/Dimodifikasi

### 1. Controller Baru
**`app/Http/Controllers/NeracaSaldoController.php`**
- Method `index()`: Tampilkan neraca saldo
- Method `exportPdf()`: Export ke PDF
- Menggunakan service untuk logika bisnis

### 2. Service Class
**`app/Services/TrialBalanceService.php`**
- `calculateTrialBalance()`: Hitung neraca saldo
- `getSaldoAwal()`: Ambil saldo awal akun
- `getMutasiPeriode()`: Ambil mutasi dari buku besar
- `calculateSaldoAkhir()`: Hitung saldo akhir
- `mapToTrialBalanceColumns()`: Map ke kolom tampilan
- `isDebitNormalAccount()`: Tentukan normal balance

### 3. View Baru
**`resources/views/akuntansi/neraca-saldo-new.blade.php`**
- Tampilan neraca saldo format standar
- Kolom: No, Kode Akun, Nama Akun, Debit, Kredit
- Balance check dan status keseimbangan
- Informasi sumber data dari buku besar

**`resources/views/akuntansi/neraca-saldo-pdf-new.blade.php`**
- Template PDF untuk neraca saldo
- Format profesional dengan header dan footer
- Informasi periode dan status keseimbangan

### 4. Routes
**`routes/web.php`**
```php
// Neraca Saldo - New version (based on General Ledger)
Route::get('/neraca-saldo-new', [NeracaSaldoController::class, 'index'])
    ->name('neraca-saldo-new');
Route::get('/neraca-saldo-new/pdf', [NeracaSaldoController::class, 'exportPdf'])
    ->name('neraca-saldo-new.pdf');
```

## Cara Penggunaan

### 1. Akses Neraca Saldo Baru
```
URL: /akuntansi/neraca-saldo-new
Route Name: akuntansi.neraca-saldo-new
```

### 2. Parameter
- **bulan**: 01-12 (default: bulan saat ini)
- **tahun**: 2020-2030 (default: tahun saat ini)

### 3. Export PDF
```
URL: /akuntansi/neraca-saldo-new/pdf?bulan=04&tahun=2026
Route Name: akuntansi.neraca-saldo-new.pdf
```

## Contoh Output

### Format Tampilan
```
No | Kode Akun | Nama Akun           | Debit        | Kredit
---|-----------|---------------------|--------------|-------------
1  | 1101      | Kas                 | 37.000.000   | -
2  | 1102      | Bank BCA            | 15.000.000   | -
3  | 2101      | Hutang Usaha        | -            | 8.500.000
4  | 4101      | Penjualan Produk    | -            | 25.000.000
5  | 5101      | Harga Pokok Penjual | 12.000.000   | -
---|-----------|---------------------|--------------|-------------
   | TOTAL     |                     | 64.000.000   | 33.500.000
   | STATUS    | TIDAK SEIMBANG      |              |
```

### Data Array yang Dikembalikan
```php
[
    'accounts' => [
        [
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'tipe_akun' => 'ASET',
            'saldo_awal' => 30000000,
            'mutasi_debit' => 10000000,
            'mutasi_kredit' => 3000000,
            'saldo_akhir' => 37000000,
            'debit' => 37000000,
            'kredit' => 0,
            'is_debit_normal' => true
        ],
        // ... akun lainnya
    ],
    'total_debit' => 64000000,
    'total_kredit' => 33500000,
    'is_balanced' => false,
    'difference' => 30500000,
    'period' => [
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'formatted_period' => '01/04/2026 - 30/04/2026'
    ]
]
```

## Keunggulan Implementasi

### 1. Akurasi Data
- ✅ Data diambil langsung dari buku besar
- ✅ Tidak ada input manual yang bisa salah
- ✅ Sinkron dengan jurnal yang sudah diposting

### 2. Standar Akuntansi
- ✅ Formula perhitungan sesuai prinsip akuntansi
- ✅ Normal balance berdasarkan tipe akun
- ✅ Handling saldo abnormal dengan benar

### 3. Validasi Otomatis
- ✅ Balance check (Total Debit = Total Kredit)
- ✅ Deteksi ketidakseimbangan
- ✅ Pesan error yang informatif

### 4. Performa
- ✅ Query optimized dengan JOIN
- ✅ Skip akun tanpa aktivitas
- ✅ Caching bisa ditambahkan di service

### 5. Maintainability
- ✅ Separation of concerns (Controller → Service)
- ✅ Dokumentasi lengkap
- ✅ Error handling yang baik

## Pengembangan Selanjutnya

### 1. Saldo Awal Dinamis
Saat ini menggunakan `saldo_awal` dari COA. Bisa dikembangkan untuk menghitung dari transaksi sebelum periode:

```php
private function getSaldoAwalFromTransactions($coaId, $beforeDate)
{
    return JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
        ->where('journal_lines.coa_id', $coaId)
        ->where('journal_entries.tanggal', '<', $beforeDate)
        ->selectRaw('SUM(debit) - SUM(credit) as saldo')
        ->value('saldo') ?? 0;
}
```

### 2. Caching
Tambahkan caching untuk performa yang lebih baik:

```php
public function calculateTrialBalance($startDate, $endDate)
{
    $cacheKey = "trial_balance_{$startDate}_{$endDate}";
    
    return Cache::remember($cacheKey, 3600, function() use ($startDate, $endDate) {
        // Logika perhitungan...
    });
}
```

### 3. Export Excel
Tambahkan export ke Excel menggunakan Laravel Excel:

```php
public function exportExcel(Request $request)
{
    // Implementation...
    return Excel::download(new TrialBalanceExport($data), 'neraca-saldo.xlsx');
}
```

### 4. Perbandingan Periode
Tambahkan fitur perbandingan neraca saldo antar periode:

```php
public function compare(Request $request)
{
    $period1 = $this->calculateTrialBalance($start1, $end1);
    $period2 = $this->calculateTrialBalance($start2, $end2);
    
    return view('akuntansi.neraca-saldo-compare', compact('period1', 'period2'));
}
```

## Testing

### 1. Unit Test untuk Service
```php
class TrialBalanceServiceTest extends TestCase
{
    public function test_calculate_trial_balance()
    {
        // Setup test data
        // Assert calculations
    }
    
    public function test_debit_normal_account_detection()
    {
        // Test normal balance detection
    }
}
```

### 2. Feature Test untuk Controller
```php
class NeracaSaldoControllerTest extends TestCase
{
    public function test_index_returns_correct_view()
    {
        $response = $this->get('/akuntansi/neraca-saldo-new');
        $response->assertStatus(200);
        $response->assertViewIs('akuntansi.neraca-saldo-new');
    }
}
```

## Kesimpulan

Implementasi ini memberikan solusi yang **akurat**, **sesuai standar akuntansi**, dan **mudah dimaintain** untuk fitur Neraca Saldo. Data diambil langsung dari buku besar sehingga selalu sinkron dengan transaksi yang sudah diposting, menghilangkan risiko kesalahan input manual.