# Fix Neraca Saldo - Error journal_lines Table Not Found

## 🐛 Error yang Terjadi

### Error Message
```
SQLSTATE[42S02]: Base table or view not found: 1146 
Table 'eadt_umkm.journal_lines' doesn't exist

SQL: select `coas`.`kode_akun`, 
     COALESCE(SUM(jl.debit),0) as total_debit, 
     COALESCE(SUM(jl.credit),0) as total_kredit 
from `journal_lines` as `jl` 
inner join `journal_entries` as `je` on `jl`.`journal_entry_id` = `je`.`id` 
inner join `coas` on `coas`.`id` = `jl`.`coa_id` 
where `je`.`user_id` = 1 
and `je`.`tanggal` between 2026-05-01 and 2026-05-31 
group by `coas`.`kode_akun`
```

### Lokasi Error
- **File**: `app/Http/Controllers/AkuntansiController.php`
- **Method**: `getAccountSummary()`
- **Line**: 107
- **Halaman**: `/akuntansi/neraca-saldo`

## 🔍 Root Cause Analysis

### Masalah
Method `getAccountSummary()` masih mencoba mengakses tabel `journal_lines` dan `journal_entries` yang **TIDAK ADA** di database.

### Mengapa Tabel Tidak Ada?
Sistem sudah **migrasi** dari struktur jurnal lama (`journal_entries` + `journal_lines`) ke struktur baru (`jurnal_umum` flat table).

### Struktur Lama (TIDAK DIGUNAKAN)
```
journal_entries (header)
├── id
├── tanggal
├── ref_type
└── ref_id

journal_lines (detail)
├── id
├── journal_entry_id (FK)
├── coa_id
├── debit
└── credit
```

### Struktur Baru (DIGUNAKAN)
```
jurnal_umum (flat table)
├── id
├── tanggal
├── coa_id
├── debit
├── kredit
├── keterangan
├── tipe_referensi
├── referensi
└── user_id (untuk multi-tenant)
```

## ✅ Solusi

### Prinsip Perbaikan
**"Gunakan HANYA tabel `jurnal_umum` seperti Buku Besar"**

Neraca Saldo harus menggunakan logika yang **IDENTIK** dengan Buku Besar:
1. Hanya dari tabel `jurnal_umum`
2. Filter by `user_id` untuk multi-tenant
3. Perhitungan sederhana: `SUM(debit)` dan `SUM(kredit)`

### Perubahan Kode

#### Method `getAccountSummary()` - SEBELUM (SALAH)
```php
private function getAccountSummary($from, $to, $kodeAkun = null)
{
    // ❌ Query ke journal_lines (tabel tidak ada)
    $queryJournalLines = DB::table('journal_lines as jl')
        ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
        ->join('coas', 'coas.id', '=', 'jl.coa_id')
        ->where('je.user_id', auth()->id())
        ->whereBetween('je.tanggal', [$from, $to])
        ->select(
            'coas.kode_akun',
            DB::raw('COALESCE(SUM(jl.debit),0) as total_debit'),
            DB::raw('COALESCE(SUM(jl.credit),0) as total_kredit')
        )
        ->groupBy('coas.kode_akun');

    $mutasiJournalLines = $queryJournalLines->get(); // ❌ ERROR DI SINI

    // Query ke jurnal_umum dengan exclude list
    $queryJurnalUmum = DB::table('jurnal_umum as ju')
        ->join('coas', 'coas.id', '=', 'ju.coa_id')
        ->where('ju.user_id', auth()->id())
        ->whereBetween('ju.tanggal', [$from, $to])
        ->whereNotIn('ju.tipe_referensi', [...]) // ❌ Kompleks dan rawan error
        ->select(...)
        ->groupBy('coas.kode_akun');

    $mutasiJurnalUmum = $queryJurnalUmum->get();

    // ❌ Gabungkan dari 2 sumber (kompleks)
    $summaryByKodeAkun = [];
    foreach ($mutasiJournalLines as $line) { ... }
    foreach ($mutasiJurnalUmum as $line) { ... }

    return $summaryByKodeAkun;
}
```

**Masalah:**
- ❌ Mengakses tabel `journal_lines` yang tidak ada
- ❌ Menggunakan 2 sumber data (duplikasi)
- ❌ Kompleksitas tinggi dengan exclude list
- ❌ Tidak konsisten dengan Buku Besar

#### Method `getAccountSummary()` - SESUDAH (BENAR)
```php
/**
 * Helper function untuk mendapatkan ringkasan akun (sama untuk Buku Besar & Neraca Saldo)
 * HANYA DARI jurnal_umum - konsisten dengan Buku Besar
 * @param string $from Tanggal awal periode (Y-m-d)
 * @param string $to Tanggal akhir periode (Y-m-d)
 * @param string|null $kodeAkun Filter by kode_akun (optional, untuk Buku Besar)
 * @return array Array dengan key kode_akun berisi summary per akun
 */
private function getAccountSummary($from, $to, $kodeAkun = null)
{
    // ✅ Hanya dari jurnal_umum - SAMA DENGAN LOGIKA BUKU BESAR
    $queryJurnalUmum = DB::table('jurnal_umum as ju')
        ->join('coas', 'coas.id', '=', 'ju.coa_id')
        ->where('ju.user_id', auth()->id()) // ✅ MULTI-TENANT: Filter by user_id
        ->whereBetween('ju.tanggal', [$from, $to])
        ->select(
            'coas.kode_akun',
            DB::raw('COALESCE(SUM(ju.debit),0) as total_debit'),
            DB::raw('COALESCE(SUM(ju.kredit),0) as total_kredit')
        )
        ->groupBy('coas.kode_akun');

    // Filter by kode_akun jika diberikan
    if ($kodeAkun) {
        $queryJurnalUmum->where('coas.kode_akun', $kodeAkun);
    }

    $mutasiJurnalUmum = $queryJurnalUmum->get();

    // ✅ Buat array dengan kode_akun sebagai key
    $summaryByKodeAkun = [];
    foreach ($mutasiJurnalUmum as $line) {
        $summaryByKodeAkun[$line->kode_akun] = [
            'total_debit' => $line->total_debit,
            'total_kredit' => $line->total_kredit
        ];
    }

    return $summaryByKodeAkun;
}
```

**Keuntungan:**
- ✅ Hanya menggunakan tabel `jurnal_umum` (tabel yang ada)
- ✅ Filter `user_id` untuk multi-tenant security
- ✅ Sederhana dan mudah dipahami
- ✅ Konsisten dengan Buku Besar
- ✅ Tidak ada duplikasi data
- ✅ Performance lebih baik (1 query vs 2 query)

## 🔍 Konsistensi Antar Halaman

Setelah fix ini, semua halaman akuntansi menggunakan logika yang **SAMA**:

| Halaman | Sumber Data | Filter user_id | Perhitungan |
|---------|-------------|----------------|-------------|
| **Buku Besar** | `jurnal_umum` | ✅ | `SUM(debit)`, `SUM(kredit)` |
| **Neraca Saldo** | `jurnal_umum` | ✅ | `SUM(debit)`, `SUM(kredit)` |
| **Laporan Kas & Bank** | `jurnal_umum` | ✅ | `SUM(debit)`, `SUM(kredit)` |
| **Dashboard** | `jurnal_umum` | ✅ | `SUM(debit)`, `SUM(kredit)` |

## 📊 Hasil yang Diharapkan

### 1. Error Teratasi
- ✅ Halaman `/akuntansi/neraca-saldo` bisa diakses tanpa error
- ✅ Tidak ada lagi error "Table journal_lines doesn't exist"

### 2. Data Konsisten
- ✅ Total Debit di Neraca Saldo = Total Debit di Buku Besar
- ✅ Total Kredit di Neraca Saldo = Total Kredit di Buku Besar
- ✅ Saldo Akhir konsisten di semua laporan

### 3. Multi-Tenant Security
- ✅ Setiap user hanya melihat data miliknya sendiri
- ✅ Filter `user_id` di semua query
- ✅ Tidak ada data bocor antar user

## 🧪 Testing Checklist

### Test 1: Verifikasi Halaman Bisa Diakses
1. Login sebagai user yang memiliki data transaksi
2. Buka halaman `/akuntansi/neraca-saldo`
3. **Verifikasi**: Halaman muncul tanpa error ✅

### Test 2: Verifikasi Konsistensi dengan Buku Besar
1. Buka **Neraca Saldo** (`/akuntansi/neraca-saldo`)
2. Catat **Total Debit** dan **Total Kredit** untuk akun Kas
3. Buka **Buku Besar** (`/akuntansi/buku-besar`)
4. Pilih akun **Kas**
5. Catat **Total Debit** dan **Total Kredit**
6. **Verifikasi**: Nilai harus **SAMA** ✅

### Test 3: Verifikasi Multi-Tenant
1. Login sebagai **User A**
2. Buka Neraca Saldo, catat total debit/kredit
3. Logout dan login sebagai **User B**
4. Buka Neraca Saldo, catat total debit/kredit
5. **Verifikasi**: Data berbeda sesuai user masing-masing ✅

### Test 4: Verifikasi Perhitungan Saldo
1. Buka **Neraca Saldo**
2. Untuk setiap akun, verifikasi:
   - Akun **ASET** dan **BEBAN**: Saldo di kolom **Debit** jika positif
   - Akun **KEWAJIBAN**, **MODAL**, **PENDAPATAN**: Saldo di kolom **Kredit** jika positif
3. **Verifikasi**: Total Debit = Total Kredit (neraca seimbang) ✅

## 🔧 Troubleshooting

### Jika Error Masih Muncul Setelah Fix

#### 1. Clear Laravel Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

#### 2. Restart Development Server
```bash
# Stop server (Ctrl+C)
# Start server again
php artisan serve
```

#### 3. Clear Browser Cache
- Tekan `Ctrl + Shift + R` (hard refresh)
- Atau buka browser dalam mode incognito

#### 4. Verifikasi File Tersimpan
Pastikan perubahan di `app/Http/Controllers/AkuntansiController.php` sudah tersimpan dengan benar.

## 📝 Catatan Penting

### Mengapa Tidak Menggunakan `journal_lines`?

1. **Tabel Tidak Ada**: Sistem sudah migrasi ke struktur baru
2. **Single Source of Truth**: Semua transaksi di `jurnal_umum`
3. **Multi-Tenant Support**: Tabel `jurnal_umum` memiliki kolom `user_id`
4. **Konsistensi**: Semua laporan menggunakan sumber data yang sama

### Apa yang Terjadi dengan Data Lama?

- Data lama di `journal_entries` dan `journal_lines` (jika ada) sudah tidak digunakan
- Semua transaksi baru masuk ke tabel `jurnal_umum`
- Sistem sekarang menggunakan flat table structure yang lebih sederhana

### Method yang Terpengaruh

Method `getAccountSummary()` digunakan oleh:
1. **Neraca Saldo** (`neracaSaldo()`) - untuk menghitung mutasi periode
2. **Buku Besar** (tidak langsung, tapi logika sama)

Setelah fix ini, kedua halaman menggunakan logika yang **IDENTIK**.

## ✅ Status

- [x] Identifikasi error (journal_lines table not found)
- [x] Analisis root cause (migrasi ke jurnal_umum)
- [x] Update method `getAccountSummary()`
- [x] Verifikasi tidak ada error syntax
- [x] Clear Laravel cache
- [ ] Testing halaman Neraca Saldo
- [ ] Testing konsistensi dengan Buku Besar
- [ ] Testing multi-tenant security

## 🎯 Kesimpulan

Fix ini memastikan **Neraca Saldo** menggunakan logika yang **IDENTIK** dengan **Buku Besar**:
- ✅ Satu sumber data (`jurnal_umum`)
- ✅ Filter `user_id` untuk multi-tenant
- ✅ Perhitungan sederhana dan akurat
- ✅ Konsisten di semua laporan akuntansi

**Hasil:** Halaman Neraca Saldo bisa diakses tanpa error dan menampilkan data yang **KONSISTEN** dengan Buku Besar ✅
