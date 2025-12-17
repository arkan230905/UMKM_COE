# Dokumentasi: Perhitungan Presensi dan Penggajian

## 1. Helper Function: PresensiHelper

File: `app/Helpers/PresensiHelper.php`

### Fungsi Utama

#### `hitungDurasiKerja(Carbon $jamMasuk, Carbon $jamKeluar): array`
Menghitung durasi kerja dari jam masuk dan jam keluar dengan pembulatan ke 0,5 jam terdekat.

**Contoh Penggunaan:**
```php
use App\Helpers\PresensiHelper;
use Carbon\Carbon;

$jamMasuk = Carbon::createFromFormat('H:i', '07:00');
$jamKeluar = Carbon::createFromFormat('H:i', '14:10');

$durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);
// Output: ['jumlah_menit_kerja' => 430, 'jumlah_jam_kerja' => 7.0]

$jamMasuk = Carbon::createFromFormat('H:i', '07:00');
$jamKeluar = Carbon::createFromFormat('H:i', '14:20');

$durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);
// Output: ['jumlah_menit_kerja' => 440, 'jumlah_jam_kerja' => 7.5]
```

#### `bulatkanKeSetengahJam(int $menit): float`
Membulatkan menit ke jam dengan kelipatan 0,5 jam terdekat.

**Logika Pembulatan:**
- 0-14 menit → 0 jam
- 15-44 menit → 0,5 jam
- 45-74 menit → 1 jam
- 75-104 menit → 1,5 jam
- dst...

**Contoh:**
```php
PresensiHelper::bulatkanKeSetengahJam(10);   // 0
PresensiHelper::bulatkanKeSetengahJam(30);   // 0.5
PresensiHelper::bulatkanKeSetengahJam(60);   // 1
PresensiHelper::bulatkanKeSetengahJam(430);  // 7
PresensiHelper::bulatkanKeSetengahJam(440);  // 7.5
PresensiHelper::bulatkanKeSetengahJam(470);  // 7.5
PresensiHelper::bulatkanKeSetengahJam(480);  // 8
```

#### `formatJamKerja(float $jamKerja): string`
Memformat jam kerja untuk tampilan UI (misal: "7 jam", "7,5 jam").

**Contoh:**
```php
PresensiHelper::formatJamKerja(7);    // "7 jam"
PresensiHelper::formatJamKerja(7.5);  // "7,5 jam"
PresensiHelper::formatJamKerja(8);    // "8 jam"
```

#### `hitungGajiPerJam(float $gajiPokokBulanan, int $jumlahHariKerjaBulanan = 22, int $jamPerHari = 8): float`
Menghitung gaji per jam dari gaji pokok bulanan.

**Asumsi Default:**
- 1 bulan = 22 hari kerja
- 1 hari kerja = 8 jam
- Total jam kerja per bulan = 176 jam

**Contoh:**
```php
$gajiPerJam = PresensiHelper::hitungGajiPerJam(2200000);
// Output: 12500 (2200000 / 176)

// Dengan parameter custom
$gajiPerJam = PresensiHelper::hitungGajiPerJam(2200000, 20, 8);
// Output: 13750 (2200000 / 160)
```

#### `hitungTotalGaji(float $gajiPerJam, float $totalJamKerja, float $tunjangan = 0, float $bonus = 0, float $potongan = 0): float`
Menghitung total gaji dengan formula:
```
total_gaji = (gaji_per_jam * total_jam_kerja) + tunjangan + bonus - potongan
```

**Contoh:**
```php
$totalGaji = PresensiHelper::hitungTotalGaji(
    gajiPerJam: 12500,
    totalJamKerja: 160,  // 20 hari × 8 jam
    tunjangan: 500000,
    bonus: 100000,
    potongan: 50000
);
// Output: 2200000 + 500000 + 100000 - 50000 = 2750000
```

---

## 2. Model Presensi

File: `app/Models/Presensi.php`

### Fitur Otomatis

#### Mutator: Hitung Durasi Kerja Otomatis
Ketika `jam_masuk` atau `jam_keluar` diubah, durasi kerja otomatis dihitung:

```php
$presensi = new Presensi();
$presensi->pegawai_id = 1;
$presensi->tgl_presensi = '2025-12-12';
$presensi->jam_masuk = '07:00';
$presensi->jam_keluar = '14:20';
// Otomatis: jumlah_menit_kerja = 440, jumlah_jam_kerja = 7.5
$presensi->save();
```

#### Accessor: Format Jam Kerja
Mengakses `jam_kerja_formatted` untuk mendapatkan format tampilan:

```php
$presensi = Presensi::find(1);
echo $presensi->jam_kerja_formatted;  // Output: "7,5 jam"
```

### Kolom Tabel Presensi

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint | Primary key |
| pegawai_id | bigint | Foreign key ke pegawai |
| tgl_presensi | date | Tanggal presensi |
| jam_masuk | string (H:i) | Jam masuk (format: 07:00) |
| jam_keluar | string (H:i) | Jam keluar (format: 14:20) |
| status | string | Status presensi (hadir, izin, sakit, dll) |
| jumlah_menit_kerja | integer | Total menit kerja (raw) |
| jumlah_jam_kerja | decimal(5,1) | Total jam kerja (dibulatkan ke 0,5 jam) |
| keterangan | text | Catatan tambahan |
| created_at | timestamp | Waktu dibuat |
| updated_at | timestamp | Waktu diupdate |

---

## 3. Query: Menjumlahkan Jam Kerja per Karyawan per Periode

### Contoh Query di Controller

```php
use App\Models\Presensi;
use Carbon\Carbon;

// Hitung total jam kerja untuk karyawan tertentu dalam periode gajian
$pegawaiId = 1;
$tanggalMulai = Carbon::createFromFormat('Y-m-d', '2025-12-01');
$tanggalAkhir = Carbon::createFromFormat('Y-m-d', '2025-12-31');

$totalJamKerja = Presensi::where('pegawai_id', $pegawaiId)
    ->whereBetween('tgl_presensi', [$tanggalMulai, $tanggalAkhir])
    ->where('status', 'hadir')  // Hanya hitung yang hadir
    ->sum('jumlah_jam_kerja');

// Output: 160 (20 hari × 8 jam)
```

### Menyimpan ke Tabel Penggajian

```php
use App\Models\Penggajian;
use App\Helpers\PresensiHelper;

$penggajian = Penggajian::find($penggajianId);

// Hitung total jam kerja dari presensi
$totalJamKerja = Presensi::where('pegawai_id', $penggajian->pegawai_id)
    ->whereBetween('tgl_presensi', [$tanggalMulai, $tanggalAkhir])
    ->where('status', 'hadir')
    ->sum('jumlah_jam_kerja');

// Update penggajian
$penggajian->total_jam_kerja = $totalJamKerja;

// Hitung gaji per jam
if ($penggajian->pegawai->kategori === 'btktl') {
    // BTKTL: Gaji pokok bulanan
    $gajiPerJam = PresensiHelper::hitungGajiPerJam($penggajian->gaji_pokok);
} else {
    // BTKL: Tarif per jam
    $gajiPerJam = $penggajian->tarif_per_jam;
}

// Hitung total gaji
$totalGaji = PresensiHelper::hitungTotalGaji(
    gajiPerJam: $gajiPerJam,
    totalJamKerja: $totalJamKerja,
    tunjangan: $penggajian->tunjangan,
    bonus: $penggajian->bonus,
    potongan: $penggajian->potongan
);

$penggajian->total_gaji = $totalGaji;
$penggajian->save();
```

---

## 4. View Blade: Tabel Presensi

File: `resources/views/transaksi/presensi/index.blade.php`

```blade
<table class="table table-striped">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Nama Pegawai</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Jumlah Jam</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($presensis as $presensi)
        <tr>
            <td>{{ $presensi->tgl_presensi->format('d/m/Y') }}</td>
            <td>{{ $presensi->pegawai->nama }}</td>
            <td>{{ $presensi->jam_masuk }}</td>
            <td>{{ $presensi->jam_keluar }}</td>
            <td>
                <span class="badge bg-info">
                    {{ $presensi->jam_kerja_formatted }}
                </span>
            </td>
            <td>
                @if($presensi->status === 'hadir')
                    <span class="badge bg-success">Hadir</span>
                @elseif($presensi->status === 'izin')
                    <span class="badge bg-warning">Izin</span>
                @elseif($presensi->status === 'sakit')
                    <span class="badge bg-danger">Sakit</span>
                @else
                    <span class="badge bg-secondary">{{ ucfirst($presensi->status) }}</span>
                @endif
            </td>
            <td>
                <a href="{{ route('transaksi.presensi.edit', $presensi->id) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

---

## 5. View Blade: Tabel Penggajian

File: `resources/views/transaksi/penggajian/index.blade.php`

```blade
<table class="table table-striped">
    <thead>
        <tr>
            <th>Nama Pegawai</th>
            <th>Periode</th>
            <th>Gaji/Tarif per Jam</th>
            <th>Total Jam Kerja</th>
            <th>Gaji Pokok</th>
            <th>Tunjangan</th>
            <th>Bonus</th>
            <th>Potongan</th>
            <th>Total Gaji</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($penggajians as $penggajian)
        <tr>
            <td>{{ $penggajian->pegawai->nama }}</td>
            <td>{{ $penggajian->tanggal_penggajian->format('M Y') }}</td>
            <td>
                Rp {{ number_format($penggajian->gaji_per_jam, 0, ',', '.') }}
            </td>
            <td>
                <span class="badge bg-info">
                    {{ number_format($penggajian->total_jam_kerja, 1, ',', '') }} jam
                </span>
            </td>
            <td>
                Rp {{ number_format($penggajian->gaji_pokok ?: ($penggajian->tarif_per_jam * $penggajian->total_jam_kerja), 0, ',', '.') }}
            </td>
            <td>
                Rp {{ number_format($penggajian->tunjangan, 0, ',', '.') }}
            </td>
            <td>
                Rp {{ number_format($penggajian->bonus, 0, ',', '.') }}
            </td>
            <td>
                Rp {{ number_format($penggajian->potongan, 0, ',', '.') }}
            </td>
            <td>
                <strong>Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}</strong>
            </td>
            <td>
                <a href="{{ route('transaksi.penggajian.show', $penggajian->id) }}" class="btn btn-sm btn-info">
                    <i class="bi bi-eye"></i> Lihat
                </a>
                <a href="{{ route('transaksi.penggajian.edit', $penggajian->id) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

---

## 6. Contoh Perhitungan Lengkap

### Skenario: Karyawan BTKL (Bayar Per Jam)

**Data Presensi (Desember 2025):**
- 1-5 Des (5 hari): 8 jam/hari = 40 jam
- 8-12 Des (5 hari): 7,5 jam/hari = 37,5 jam
- 15-19 Des (5 hari): 8 jam/hari = 40 jam
- Total: 20 hari = 117,5 jam

**Data Penggajian:**
- Tarif per jam: Rp 12.500
- Tunjangan Jabatan: Rp 500.000
- Bonus: Rp 100.000
- Potongan: Rp 50.000

**Perhitungan:**
```
Gaji Pokok = 12.500 × 117,5 = 1.468.750
Total Gaji = 1.468.750 + 500.000 + 100.000 - 50.000 = 2.018.750
```

### Skenario: Karyawan BTKTL (Gaji Bulanan)

**Data Presensi (Desember 2025):**
- Total: 20 hari = 160 jam (20 hari × 8 jam)

**Data Penggajian:**
- Gaji Pokok Bulanan: Rp 2.200.000
- Tunjangan Jabatan: Rp 500.000
- Bonus: Rp 100.000
- Potongan: Rp 50.000

**Perhitungan:**
```
Gaji per Jam = 2.200.000 / (22 hari × 8 jam) = 2.200.000 / 176 = 12.500
Gaji Pokok = 12.500 × 160 = 2.000.000
Total Gaji = 2.000.000 + 500.000 + 100.000 - 50.000 = 2.550.000
```

---

## 7. Catatan Penting

1. **Pembulatan Jam Kerja**: Semua jam kerja di UI hanya menampilkan kelipatan 0,5 jam (7, 7.5, 8, dst).

2. **Format Rupiah**: Gunakan `number_format()` dengan separator ribuan (.) dan desimal (,) sesuai format Indonesia.

3. **Validasi Jam Masuk/Keluar**: Pastikan jam_keluar > jam_masuk. Jika jam_keluar < jam_masuk, anggap hari berikutnya.

4. **Hari Kerja Bulanan**: Default 22 hari kerja per bulan. Sesuaikan jika ada hari libur nasional.

5. **Status Presensi**: Hanya hitung jam kerja untuk status "hadir". Izin/sakit tidak dihitung atau dihitung dengan nilai khusus.

---

## 8. Testing

### Unit Test Helper

```php
// tests/Unit/PresensiHelperTest.php
use App\Helpers\PresensiHelper;
use Carbon\Carbon;

public function test_hitung_durasi_kerja()
{
    $jamMasuk = Carbon::createFromFormat('H:i', '07:00');
    $jamKeluar = Carbon::createFromFormat('H:i', '14:20');
    
    $durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);
    
    $this->assertEquals(440, $durasi['jumlah_menit_kerja']);
    $this->assertEquals(7.5, $durasi['jumlah_jam_kerja']);
}

public function test_format_jam_kerja()
{
    $this->assertEquals('7 jam', PresensiHelper::formatJamKerja(7));
    $this->assertEquals('7,5 jam', PresensiHelper::formatJamKerja(7.5));
    $this->assertEquals('8 jam', PresensiHelper::formatJamKerja(8));
}

public function test_hitung_gaji_per_jam()
{
    $gajiPerJam = PresensiHelper::hitungGajiPerJam(2200000);
    $this->assertEquals(12500, $gajiPerJam);
}

public function test_hitung_total_gaji()
{
    $totalGaji = PresensiHelper::hitungTotalGaji(
        gajiPerJam: 12500,
        totalJamKerja: 160,
        tunjangan: 500000,
        bonus: 100000,
        potongan: 50000
    );
    
    $this->assertEquals(2550000, $totalGaji);
}
```

---

**Dibuat**: 12 Desember 2025
**Versi**: 1.0
