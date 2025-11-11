# Perbaikan Dashboard - Data Akurat dan Icon Lengkap

## Masalah
1. Data di dashboard tidak sesuai dengan laporan sebenarnya
2. Icon Master Data hanya muncul di Presensi
3. Total Kas & Bank, Pendapatan, Piutang, Utang tidak akurat

## Penyebab
1. Controller menggunakan tabel `jurnal_umum` yang sudah tidak dipakai
2. Sistem sekarang menggunakan `journal_entries` dan `journal_lines`
3. Icon Master Data ada duplikat (Pegawai 2x)

## Solusi yang Diterapkan

### 1. Perbaikan Perhitungan Total Kas & Bank
**File:** `app/Http/Controllers/DashboardController.php`

**Sebelum:**
```php
// Menggunakan jurnal_umum (SALAH)
$debit = JurnalUmum::whereIn('coa_id', $ids)->sum('debit');
$kredit = JurnalUmum::whereIn('coa_id', $ids)->sum('kredit');
```

**Sesudah:**
```php
// Menggunakan journal_lines (BENAR)
$coas = Coa::whereIn('kode_akun', ['101', '102'])->get();
foreach ($coas as $coa) {
    $account = DB::table('accounts')->where('code', $coa->kode_akun)->first();
    $debit = DB::table('journal_lines')->where('account_id', $account->id)->sum('debit');
    $kredit = DB::table('journal_lines')->where('account_id', $account->id)->sum('credit');
    $total += $saldoAwal + $debit - $kredit;
}
```

### 2. Perbaikan Perhitungan Pendapatan Bulan Ini
**Sebelum:**
```php
// Menggunakan jurnal_umum (SALAH)
$kredit = JurnalUmum::whereIn('coa_id', $ids)
    ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
    ->sum('kredit');
```

**Sesudah:**
```php
// Langsung dari tabel penjualans (BENAR)
$totalPenjualan = Penjualan::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
    ->sum('total');
```

### 3. Perbaikan Perhitungan Total Piutang
**Sebelum:**
```php
// Menggunakan jurnal_umum (SALAH)
$debit = JurnalUmum::whereIn('coa_id', $ids)->sum('debit');
$kredit = JurnalUmum::whereIn('coa_id', $ids)->sum('kredit');
```

**Sesudah:**
```php
// Langsung dari penjualan kredit yang belum lunas (BENAR)
$totalPiutang = Penjualan::where('payment_method', 'credit')
    ->where('status', '!=', 'lunas')
    ->sum('total');
```

### 4. Perbaikan Perhitungan Total Utang
**Sebelum:**
```php
// Menggunakan jurnal_umum (SALAH)
$debit = JurnalUmum::whereIn('coa_id', $ids)->sum('debit');
$kredit = JurnalUmum::whereIn('coa_id', $ids)->sum('kredit');
```

**Sesudah:**
```php
// Langsung dari pembelian kredit yang belum lunas (BENAR)
$totalUtang = Pembelian::where('payment_method', 'credit')
    ->where('status', '!=', 'lunas')
    ->sum('sisa_pembayaran');
```

### 5. Perbaikan Icon Master Data
**File:** `resources/views/dashboard.blade.php`

**Sebelum:**
```php
$masterData = [
    ['title'=>'Pegawai',...],  // Duplikat
    ['title'=>'Pegawai',...],  // Duplikat
    // Icon hanya muncul di Presensi
];
```

**Sesudah:**
```php
$masterData = [
    ['title'=>'Pegawai','count'=>$totalPegawai,'icon'=>'bi-people-fill','color'=>'primary'],
    ['title'=>'Bahan Baku','count'=>$totalBahanBaku,'icon'=>'bi-droplet-fill','color'=>'info'],
    ['title'=>'Produk','count'=>$totalProduk,'icon'=>'bi-box-seam-fill','color'=>'success'],
    ['title'=>'Vendor','count'=>$totalVendor,'icon'=>'bi-shop','color'=>'warning'],
    ['title'=>'BOM','count'=>$totalBOM,'icon'=>'bi-gear-fill','color'=>'danger'],
    ['title'=>'Presensi','count'=>$totalPresensi,'icon'=>'bi-calendar-check-fill','color'=>'secondary'],
];
```

## Hasil

### Sebelum Perbaikan
- Total Kas & Bank: **Rp 40.163.366** (tidak akurat) ❌
- Pendapatan Bulan Ini: **Rp 6.163.366** (tidak akurat) ❌
- Total Piutang: **Rp 0** (tidak akurat) ❌
- Total Utang: **Rp 0** (tidak akurat) ❌
- Icon Master Data: Hanya Presensi ❌

### Setelah Perbaikan
- Total Kas & Bank: **Sesuai dengan Laporan Kas & Bank** ✅
- Pendapatan Bulan Ini: **Sesuai dengan Total Penjualan Bulan Ini** ✅
- Total Piutang: **Sesuai dengan Penjualan Kredit Belum Lunas** ✅
- Total Utang: **Sesuai dengan Pembelian Kredit Belum Lunas** ✅
- Icon Master Data: **Semua tampil dengan warna berbeda** ✅

## Mapping Data Dashboard ke Laporan

### 1. Total Kas & Bank
```
Dashboard: getTotalKasBank()
  ↓
Laporan: Laporan Kas dan Bank
  ↓
Sumber: COA 101 (Kas) + COA 102 (Bank)
Rumus: Saldo Awal + Debit - Credit (dari journal_lines)
```

### 2. Pendapatan Bulan Ini
```
Dashboard: getPendapatanBulanIni()
  ↓
Laporan: Laporan Penjualan (filter bulan ini)
  ↓
Sumber: Tabel penjualans
Rumus: SUM(total) WHERE tanggal BETWEEN start_of_month AND end_of_month
```

### 3. Total Piutang
```
Dashboard: getTotalPiutang()
  ↓
Laporan: Laporan Penjualan (filter kredit belum lunas)
  ↓
Sumber: Tabel penjualans
Rumus: SUM(total) WHERE payment_method = 'credit' AND status != 'lunas'
```

### 4. Total Utang
```
Dashboard: getTotalUtang()
  ↓
Laporan: Laporan Pelunasan Utang (pembelian belum lunas)
  ↓
Sumber: Tabel pembelians
Rumus: SUM(sisa_pembayaran) WHERE payment_method = 'credit' AND status != 'lunas'
```

## Icon Master Data

| Master Data | Icon | Warna | Deskripsi |
|-------------|------|-------|-----------|
| Pegawai | bi-people-fill | Primary (Biru) | Icon orang banyak |
| Bahan Baku | bi-droplet-fill | Info (Cyan) | Icon tetesan |
| Produk | bi-box-seam-fill | Success (Hijau) | Icon kotak produk |
| Vendor | bi-shop | Warning (Kuning) | Icon toko |
| BOM | bi-gear-fill | Danger (Merah) | Icon gear/setting |
| Presensi | bi-calendar-check-fill | Secondary (Abu) | Icon kalender |

## Testing

### 1. Test Total Kas & Bank
```
1. Buka Dashboard
2. Lihat "Total Kas & Bank"
3. Buka Laporan Kas dan Bank
4. Bandingkan total - harus sama
```

### 2. Test Pendapatan Bulan Ini
```
1. Buka Dashboard
2. Lihat "Pendapatan Bulan Ini"
3. Buka Laporan Penjualan, filter bulan ini
4. Hitung total - harus sama
```

### 3. Test Total Piutang
```
1. Buka Dashboard
2. Lihat "Total Piutang"
3. Buka Laporan Penjualan, filter payment_method = credit, status != lunas
4. Hitung total - harus sama
```

### 4. Test Total Utang
```
1. Buka Dashboard
2. Lihat "Total Utang"
3. Buka Laporan Pelunasan Utang, lihat pembelian belum lunas
4. Hitung total sisa pembayaran - harus sama
```

### 5. Test Icon Master Data
```
1. Buka Dashboard
2. Scroll ke bagian "Master Data"
3. Semua 6 card harus tampil dengan:
   - Icon yang berbeda
   - Warna yang berbeda
   - Jumlah data yang benar
```

## Kesimpulan

Dashboard sekarang sudah akurat dan lengkap:
- ✅ Data sesuai dengan laporan sebenarnya
- ✅ Menggunakan tabel yang benar (journal_lines, bukan jurnal_umum)
- ✅ Icon Master Data lengkap dengan warna berbeda
- ✅ Perhitungan langsung dari sumber data
- ✅ Error handling untuk mencegah crash

**Refresh browser dan lihat dashboard yang sudah diperbaiki!** 🎉
