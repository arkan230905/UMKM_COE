# Ringkasan Implementasi: Perhitungan Presensi dan Penggajian

**Tanggal**: 12 Desember 2025  
**Status**: ✅ Selesai (Fase 1 - Backend & Logic)

---

## 📋 Apa yang Telah Dikerjakan

### 1. ✅ Helper Function: `PresensiHelper.php`
**File**: `app/Helpers/PresensiHelper.php`

**Fungsi Utama:**
- `hitungDurasiKerja(Carbon $jamMasuk, Carbon $jamKeluar)` - Hitung durasi kerja dengan pembulatan ke 0,5 jam
- `bulatkanKeSetengahJam(int $menit)` - Bulatkan menit ke jam dengan kelipatan 0,5 jam
- `formatJamKerja(float $jamKerja)` - Format jam kerja untuk tampilan (7, 7.5, 8 jam)
- `hitungGajiPerJam(float $gajiPokokBulanan)` - Hitung gaji per jam dari gaji pokok bulanan
- `hitungTotalGaji(float $gajiPerJam, float $totalJamKerja, ...)` - Hitung total gaji dengan formula lengkap

**Contoh Penggunaan:**
```php
use App\Helpers\PresensiHelper;
use Carbon\Carbon;

// Hitung durasi kerja
$jamMasuk = Carbon::createFromFormat('H:i', '07:00');
$jamKeluar = Carbon::createFromFormat('H:i', '14:20');
$durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);
// Output: ['jumlah_menit_kerja' => 440, 'jumlah_jam_kerja' => 7.5]

// Format jam kerja
echo PresensiHelper::formatJamKerja(7.5);  // Output: "7,5 jam"

// Hitung gaji per jam
$gajiPerJam = PresensiHelper::hitungGajiPerJam(2200000);  // Output: 12500

// Hitung total gaji
$totalGaji = PresensiHelper::hitungTotalGaji(12500, 160, 500000, 100000, 50000);
// Output: 2550000
```

---

### 2. ✅ Migration: Tambah Kolom ke Tabel Presensi
**File**: `database/migrations/2025_12_12_000005_add_durasi_kerja_to_presensis_table.php`

**Kolom Baru:**
- `jumlah_menit_kerja` (integer) - Total menit kerja (raw)
- `jumlah_jam_kerja` (decimal 5,1) - Total jam kerja (dibulatkan ke 0,5 jam)

**Fitur:**
- Menggunakan `Schema::hasColumn()` untuk cek kolom sudah ada atau belum
- Aman untuk dijalankan berkali-kali tanpa error

---

### 3. ✅ Model Presensi: Accessor & Mutator
**File**: `app/Models/Presensi.php`

**Fitur Otomatis:**
- **Mutator**: Saat `jam_masuk` atau `jam_keluar` diubah, durasi kerja otomatis dihitung
- **Accessor**: `jam_kerja_formatted` - Mengakses format jam kerja yang rapi

**Contoh:**
```php
$presensi = new Presensi();
$presensi->jam_masuk = '07:00';
$presensi->jam_keluar = '14:20';
// Otomatis: jumlah_menit_kerja = 440, jumlah_jam_kerja = 7.5

// Di view
echo $presensi->jam_kerja_formatted;  // Output: "7,5 jam"
```

---

### 4. ✅ PresensiController: Integrasi PresensiHelper
**File**: `app/Http/Controllers/PresensiController.php`

**Perubahan:**
- Import `PresensiHelper`
- Method `store()` - Gunakan `PresensiHelper::hitungDurasiKerja()` untuk hitung durasi
- Method `update()` - Gunakan `PresensiHelper::hitungDurasiKerja()` untuk hitung ulang durasi
- Simpan `jumlah_menit_kerja` dan `jumlah_jam_kerja` ke database

**Contoh Kode:**
```php
use App\Helpers\PresensiHelper;

// Di method store atau update
$jamMasuk = Carbon::createFromFormat('H:i', $validated['jam_masuk']);
$jamKeluar = Carbon::createFromFormat('H:i', $validated['jam_keluar']);

$durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);
$validated['jumlah_menit_kerja'] = $durasi['jumlah_menit_kerja'];
$validated['jumlah_jam_kerja'] = $durasi['jumlah_jam_kerja'];

Presensi::create($validated);
```

---

### 5. ✅ Dokumentasi Lengkap
**File**: `DOKUMENTASI_PRESENSI_PENGGAJIAN.md`

Berisi:
- Penjelasan detail setiap fungsi di PresensiHelper
- Contoh query Eloquent untuk menjumlahkan jam kerja per karyawan
- Contoh kode Blade untuk tampilan Presensi dan Penggajian
- Contoh perhitungan lengkap (BTKL dan BTKTL)
- Unit test examples
- Catatan penting dan tips implementasi

---

### 6. ✅ Contoh Implementasi Lengkap
**File**: `CONTOH_IMPLEMENTASI_PRESENSI_PENGGAJIAN.php`

Berisi:
- Contoh PresensiController dengan integrasi PresensiHelper
- Contoh PenggajianController dengan perhitungan gaji
- Contoh Model Presensi dengan mutator
- Contoh View Blade untuk Presensi dan Penggajian
- Contoh Unit Test

---

## 🎯 Logika Pembulatan Jam Kerja

Semua jam kerja dibulatkan ke kelipatan 0,5 jam terdekat:

| Durasi | Hasil |
|--------|-------|
| 7 jam 0-14 menit | 7,0 jam |
| 7 jam 15-44 menit | 7,5 jam |
| 7 jam 45-74 menit | 8,0 jam |
| 7 jam 75+ menit | 8,5 jam |

**Rumus**: `round(jam * 2) / 2`

---

## 📊 Formula Perhitungan Gaji

### Untuk BTKL (Bayar Per Jam):
```
Gaji per Jam = Tarif per Jam (dari master jabatan)
Total Gaji = (Gaji per Jam × Total Jam Kerja) + Tunjangan + Bonus - Potongan
```

### Untuk BTKTL (Gaji Bulanan):
```
Gaji per Jam = Gaji Pokok Bulanan / (22 hari × 8 jam) = Gaji Pokok / 176
Total Gaji = (Gaji per Jam × Total Jam Kerja) + Tunjangan + Bonus - Potongan
```

---

## 🔄 Alur Kerja Lengkap

### 1. Input Presensi
```
User input jam_masuk & jam_keluar
    ↓
PresensiController::store()
    ↓
PresensiHelper::hitungDurasiKerja()
    ↓
Simpan: jumlah_menit_kerja, jumlah_jam_kerja
    ↓
Presensi tersimpan dengan jam kerja yang sudah dibulatkan ke 0,5 jam
```

### 2. Hitung Penggajian
```
Query: Presensi::where('status', 'hadir')->sum('jumlah_jam_kerja')
    ↓
Total Jam Kerja (dalam kelipatan 0,5 jam)
    ↓
PresensiHelper::hitungGajiPerJam()
    ↓
PresensiHelper::hitungTotalGaji()
    ↓
Penggajian tersimpan dengan total gaji yang akurat
```

### 3. Tampilkan di View
```
Presensi: {{ $presensi->jam_kerja_formatted }}  // "7,5 jam"
Penggajian: {{ number_format($penggajian->total_jam_kerja, 1, ',', '') }} jam
Gaji: Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}
```

---

## 📝 Contoh Query untuk Penggajian

### Hitung Total Jam Kerja per Karyawan per Periode:
```php
use App\Models\Presensi;
use Carbon\Carbon;

$pegawaiId = 1;
$tanggalMulai = Carbon::createFromFormat('Y-m-d', '2025-12-01');
$tanggalAkhir = Carbon::createFromFormat('Y-m-d', '2025-12-31');

$totalJamKerja = Presensi::where('pegawai_id', $pegawaiId)
    ->whereBetween('tgl_presensi', [$tanggalMulai, $tanggalAkhir])
    ->where('status', 'Hadir')
    ->sum('jumlah_jam_kerja');

// Output: 160 (20 hari × 8 jam)
```

### Simpan ke Penggajian:
```php
use App\Models\Penggajian;
use App\Helpers\PresensiHelper;

$penggajian = Penggajian::find($penggajianId);

// Hitung total jam kerja
$totalJamKerja = Presensi::where('pegawai_id', $penggajian->pegawai_id)
    ->whereBetween('tgl_presensi', [$tanggalMulai, $tanggalAkhir])
    ->where('status', 'Hadir')
    ->sum('jumlah_jam_kerja');

// Hitung gaji per jam
$gajiPerJam = PresensiHelper::hitungGajiPerJam($penggajian->gaji_pokok);

// Hitung total gaji
$totalGaji = PresensiHelper::hitungTotalGaji(
    gajiPerJam: $gajiPerJam,
    totalJamKerja: $totalJamKerja,
    tunjangan: $penggajian->tunjangan,
    bonus: $penggajian->bonus,
    potongan: $penggajian->potongan
);

// Update penggajian
$penggajian->update([
    'total_jam_kerja' => $totalJamKerja,
    'gaji_per_jam' => $gajiPerJam,
    'total_gaji' => $totalGaji
]);
```

---

## 🚀 Langkah Implementasi di Aplikasi Anda

### 1. Copy File Helper
```bash
# File sudah ada di: app/Helpers/PresensiHelper.php
```

### 2. Jalankan Migration
```bash
php artisan migrate
```

### 3. Update Model Presensi
```php
// Tambahkan import dan kolom ke $fillable
use App\Helpers\PresensiHelper;

protected $fillable = [
    // ... existing fields
    'jumlah_menit_kerja',
    'jumlah_jam_kerja',
];
```

### 4. Update PresensiController
```php
// Import PresensiHelper
use App\Helpers\PresensiHelper;

// Di method store dan update, gunakan:
$durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);
$validated['jumlah_menit_kerja'] = $durasi['jumlah_menit_kerja'];
$validated['jumlah_jam_kerja'] = $durasi['jumlah_jam_kerja'];
```

### 5. Update View Presensi
```blade
<!-- Tampilkan jam kerja yang rapi -->
<span class="badge bg-info">
    {{ $presensi->jam_kerja_formatted }}
</span>
```

### 6. Update PenggajianController
```php
// Hitung total jam kerja dari presensi
$totalJamKerja = Presensi::where('pegawai_id', $pegawaiId)
    ->whereBetween('tgl_presensi', [$tanggalMulai, $tanggalAkhir])
    ->where('status', 'Hadir')
    ->sum('jumlah_jam_kerja');

// Hitung gaji
$gajiPerJam = PresensiHelper::hitungGajiPerJam($gajiPokok);
$totalGaji = PresensiHelper::hitungTotalGaji($gajiPerJam, $totalJamKerja, ...);
```

### 7. Update View Penggajian
```blade
<!-- Tampilkan jam kerja dan gaji yang rapi -->
<td>{{ number_format($penggajian->total_jam_kerja, 1, ',', '') }} jam</td>
<td>Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}</td>
```

---

## ✨ Keuntungan Implementasi Ini

1. **Konsistensi**: Semua jam kerja di UI hanya dalam kelipatan 0,5 jam
2. **Akurasi**: Pembulatan menggunakan rumus matematis yang tepat
3. **Otomatis**: Mutator model menghitung durasi otomatis saat jam diubah
4. **Reusable**: Helper function dapat digunakan di mana saja
5. **Mudah Dipahami**: Format tampilan sederhana dan jelas
6. **Backward Compatible**: Kolom `jumlah_jam` tetap ada untuk kompatibilitas

---

## 📚 File yang Dibuat/Dimodifikasi

### ✅ File Baru:
1. `app/Helpers/PresensiHelper.php` - Helper function
2. `database/migrations/2025_12_12_000005_add_durasi_kerja_to_presensis_table.php` - Migration
3. `DOKUMENTASI_PRESENSI_PENGGAJIAN.md` - Dokumentasi lengkap
4. `CONTOH_IMPLEMENTASI_PRESENSI_PENGGAJIAN.php` - Contoh implementasi
5. `RINGKASAN_IMPLEMENTASI_PRESENSI_PENGGAJIAN.md` - File ini

### ✅ File Dimodifikasi:
1. `app/Models/Presensi.php` - Tambah accessor & mutator
2. `app/Http/Controllers/PresensiController.php` - Integrasi PresensiHelper

---

## 🧪 Testing

### Unit Test untuk PresensiHelper:
```php
// tests/Unit/PresensiHelperTest.php

public function test_durasi_kerja_7_jam_20_menit()
{
    $jamMasuk = Carbon::createFromFormat('H:i', '07:00');
    $jamKeluar = Carbon::createFromFormat('H:i', '14:20');
    
    $durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);
    
    $this->assertEquals(440, $durasi['jumlah_menit_kerja']);
    $this->assertEquals(7.5, $durasi['jumlah_jam_kerja']);
}
```

### Jalankan Test:
```bash
php artisan test
```

---

## 📌 Catatan Penting

1. **Format Jam**: Selalu gunakan format `H:i` (24-jam) untuk jam masuk/keluar
2. **Validasi**: Pastikan `jam_keluar > jam_masuk` di validasi form
3. **Status Presensi**: Hanya hitung jam kerja untuk status "Hadir"
4. **Hari Kerja**: Default 22 hari kerja per bulan (sesuaikan jika ada hari libur nasional)
5. **Backward Compatibility**: Kolom `jumlah_jam` tetap diisi untuk kompatibilitas dengan kode lama

---

## 🎓 Kesimpulan

Implementasi ini memberikan solusi lengkap untuk:
- ✅ Menghitung durasi kerja dengan pembulatan ke 0,5 jam
- ✅ Menyimpan jam kerja yang rapi dan konsisten
- ✅ Menampilkan jam kerja dan gaji dengan format yang mudah dipahami
- ✅ Menghitung gaji per jam dan total gaji dengan akurat
- ✅ Reusable dan mudah diintegrasikan ke aplikasi

**Status**: Siap untuk implementasi di aplikasi Anda!

---

**Dibuat**: 12 Desember 2025  
**Versi**: 1.0  
**Status**: ✅ Selesai
