# Perbaikan Neraca Saldo - Konsistensi dengan Buku Besar

## Masalah yang Diperbaiki

Neraca Saldo menampilkan nilai yang berbeda dengan Buku Besar untuk akun yang sama pada periode yang sama.

### Contoh Kasus:
- **Buku Besar** akun 1153 (Pers. Bahan Pendukung Kemasan) periode Mei 2026:
  - Saldo awal: Rp 0
  - Transaksi 13/05/2026 debit: Rp 100.000
  - **Saldo akhir: Rp 100.000**

- **Neraca Saldo** akun 1153 periode Mei 2026 (SEBELUM PERBAIKAN):
  - Menampilkan: **Rp 210.000** (SALAH - mengambil data dari user lain juga!)

## Root Cause

Ditemukan **2 masalah kritis** di `TrialBalanceService.php`:

### 1. Missing Multi-Tenant Filter di `getMutasiPeriode()`

Method ini **TIDAK memfilter berdasarkan user_id**, sehingga mengambil transaksi dari **SEMUA user** yang memiliki kode akun yang sama.

**SEBELUM:**
```php
$mutasi = DB::table('jurnal_umum as ju')
    ->join('coas', 'ju.coa_id', '=', 'coas.id')
    ->where('coas.kode_akun', $coa->kode_akun)
    ->whereBetween('ju.tanggal', [$startDate, $endDate])
    // MISSING: ->where('ju.user_id', auth()->id())
    ->selectRaw('...')
    ->first();
```

### 2. Missing Multi-Tenant Filter di `getSaldoAkhirFromBukuBesar()`

Method ini juga **TIDAK memfilter berdasarkan user_id**.

**SEBELUM:**
```php
$journalLines = DB::table('jurnal_umum as ju')
    ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
    ->where(function($q) use ($coa) {
        $q->where('coas.kode_akun', $coa->kode_akun)
          ->orWhere('coas.id', $coa->id);
    })
    // MISSING: ->where('ju.user_id', auth()->id())
    ->where('ju.tanggal', '<=', $endDate)
    ->get();
```

**Akibatnya:** Neraca Saldo menghitung transaksi dari user lain yang kebetulan memiliki kode akun yang sama (misal: user 1 dan user 2 sama-sama punya akun 1153).

## Solusi yang Diterapkan

### 1. Tambahkan Filter Multi-Tenant di `getMutasiPeriode()`

File: `app/Services/TrialBalanceService.php`

```php
private function getMutasiPeriode($coaId, $startDate, $endDate)
{
    $coa = Coa::find($coaId);
    if (!$coa) {
        return ['total_debit' => 0, 'total_kredit' => 0];
    }
    
    $mutasi = DB::table('jurnal_umum as ju')
        ->join('coas', 'ju.coa_id', '=', 'coas.id')
        ->where('ju.user_id', auth()->id()) // ✅ PERBAIKAN: Filter by user_id
        ->where('coas.kode_akun', $coa->kode_akun)
        ->whereBetween('ju.tanggal', [$startDate, $endDate])
        ->selectRaw('
            COALESCE(SUM(ju.debit), 0) as total_debit,
            COALESCE(SUM(ju.kredit), 0) as total_kredit
        ')
        ->first();

    return [
        'total_debit' => (float) ($mutasi->total_debit ?? 0),
        'total_kredit' => (float) ($mutasi->total_kredit ?? 0)
    ];
}
```

### 2. Tambahkan Filter Multi-Tenant di `getSaldoAkhirFromBukuBesar()`

```php
$journalLines = DB::table('jurnal_umum as ju')
    ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
    ->where('ju.user_id', auth()->id()) // ✅ PERBAIKAN: Filter by user_id
    ->where(function($q) use ($coa) {
        $q->where('coas.kode_akun', $coa->kode_akun)
          ->orWhere('coas.id', $coa->id);
    })
    ->where('ju.tanggal', '<=', $endDate)
    ->select([
        'ju.debit',
        'ju.kredit',
        'coas.kode_akun',
        'coas.id as coa_id'
    ])
    ->get();
```

### 3. Formula Perhitungan Saldo Akhir (Sudah Benar)

Formula yang digunakan sudah sama dengan Buku Besar:

```php
private function calculateSaldoAkhir($coa, $saldoAwal, $totalDebit, $totalKredit)
{
    // Formula UNIVERSAL untuk SEMUA akun (sama dengan Buku Besar)
    return $saldoAwal + $totalDebit - $totalKredit;
}
```

## Hasil Setelah Perbaikan

### Test Akun 1153 - Pers. Bahan Pendukung Kemasan

**SEBELUM Perbaikan:**
- Buku Besar: Rp 100.000
- Neraca Saldo: Rp 210.000 ❌ (mengambil data user lain)

**SETELAH Perbaikan:**
- Buku Besar: Rp 100.000
- Neraca Saldo: Rp 100.000 ✅ (konsisten!)

### Test Akun 1141 - Pers. Bahan Baku Jagung

**SETELAH Perbaikan:**
- Buku Besar: Rp 1.250.000
- Neraca Saldo: Rp 1.250.000 ✅ (konsisten!)

## Verifikasi

### Cara Memverifikasi:
1. Buka **Buku Besar** untuk akun tertentu (misal: 1153) pada periode tertentu (misal: Mei 2026)
2. Catat **Saldo Akhir** yang ditampilkan
3. Buka **Neraca Saldo** untuk periode yang sama (Mei 2026)
4. Cari akun yang sama (1153)
5. Nilai di kolom Debit/Kredit harus **SAMA PERSIS** dengan Saldo Akhir di Buku Besar

### Script Debug:
Gunakan script `debug_neraca_vs_buku_besar.php` untuk memverifikasi:

```bash
php debug_neraca_vs_buku_besar.php
```

Script ini akan membandingkan perhitungan Buku Besar vs Neraca Saldo dan menampilkan:
- ✅ SAMA - jika konsisten
- ❌ BEDA - jika masih ada masalah

## File yang Diubah

1. **app/Services/TrialBalanceService.php**
   - Method: `getMutasiPeriode()` - Tambah filter `user_id`
   - Method: `getSaldoAkhirFromBukuBesar()` - Tambah filter `user_id`
   - Method: `calculateSaldoAkhir()` - Gunakan formula universal

## Catatan Penting

- **Multi-Tenant Critical:** Filter `user_id` sangat penting untuk memastikan setiap user hanya melihat data mereka sendiri
- **Tidak ada perubahan** pada tampilan, struktur tabel, filter, atau tombol export
- **Tidak ada perubahan** pada logika halaman lain (Buku Besar, Jurnal Umum, dll)
- **Cache sudah di-clear** untuk memastikan perubahan langsung berlaku

## Kesimpulan

Masalah utama adalah **missing multi-tenant filter** di 2 method kritis di `TrialBalanceService`:
1. `getMutasiPeriode()` - mengambil mutasi periode
2. `getSaldoAkhirFromBukuBesar()` - mengambil saldo akhir

Setelah menambahkan filter `->where('ju.user_id', auth()->id())`, Neraca Saldo sekarang menampilkan nilai yang **sama persis** dengan Buku Besar.

**Formula Universal yang Digunakan:**
```
Saldo Akhir = Saldo Awal + Total Debit - Total Kredit
```

Formula ini berlaku untuk **SEMUA akun** dan **HANYA mengambil data user yang sedang login**.
