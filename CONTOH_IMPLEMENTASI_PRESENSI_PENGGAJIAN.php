<?php

/**
 * CONTOH IMPLEMENTASI: Perhitungan Presensi dan Penggajian
 * 
 * File ini menunjukkan cara mengintegrasikan PresensiHelper ke dalam
 * Controller, Model, dan View untuk perhitungan presensi dan penggajian
 * yang rapi dan terstruktur.
 */

// ============================================================================
// 1. CONTOH: PresensiController - Menyimpan Presensi
// ============================================================================

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Pegawai;
use App\Helpers\PresensiHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresensiControllerExample extends Controller
{
    /**
     * Simpan presensi baru
     * 
     * Mutator di model Presensi otomatis menghitung:
     * - jumlah_menit_kerja
     * - jumlah_jam_kerja (dibulatkan ke 0,5 jam)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tgl_presensi' => 'required|date',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'status' => 'required|in:hadir,izin,sakit,cuti',
            'keterangan' => 'nullable|string',
        ]);

        // Buat presensi baru
        $presensi = new Presensi($validated);
        
        // Mutator otomatis menghitung durasi kerja
        // Tidak perlu manual: $presensi->jumlah_menit_kerja = ...
        // Tidak perlu manual: $presensi->jumlah_jam_kerja = ...
        
        $presensi->save();

        return redirect()->route('transaksi.presensi.index')
            ->with('success', 'Presensi berhasil disimpan');
    }

    /**
     * Update presensi
     */
    public function update(Request $request, Presensi $presensi)
    {
        $validated = $request->validate([
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'status' => 'required|in:hadir,izin,sakit,cuti',
            'keterangan' => 'nullable|string',
        ]);

        // Update presensi
        $presensi->update($validated);
        // Mutator otomatis menghitung ulang durasi kerja

        return redirect()->route('transaksi.presensi.index')
            ->with('success', 'Presensi berhasil diperbarui');
    }

    /**
     * Tampilkan daftar presensi dengan format rapi
     */
    public function index()
    {
        $presensis = Presensi::with('pegawai')
            ->orderBy('tgl_presensi', 'desc')
            ->paginate(20);

        return view('transaksi.presensi.index', compact('presensis'));
    }
}

// ============================================================================
// 2. CONTOH: PenggajianController - Menghitung Gaji
// ============================================================================

namespace App\Http\Controllers;

use App\Models\Penggajian;
use App\Models\Presensi;
use App\Helpers\PresensiHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PenggajianControllerExample extends Controller
{
    /**
     * Hitung dan simpan penggajian untuk satu karyawan dalam satu periode
     */
    public function hitungGajiKaryawan($pegawaiId, $tanggalMulai, $tanggalAkhir)
    {
        // 1. Ambil data karyawan
        $pegawai = Pegawai::findOrFail($pegawaiId);

        // 2. Hitung total jam kerja dari presensi
        $totalJamKerja = Presensi::where('pegawai_id', $pegawaiId)
            ->whereBetween('tgl_presensi', [$tanggalMulai, $tanggalAkhir])
            ->where('status', 'hadir')
            ->sum('jumlah_jam_kerja');

        // 3. Tentukan gaji per jam
        if ($pegawai->kategori === 'btktl') {
            // BTKTL: Gaji pokok bulanan
            $gajiPerJam = PresensiHelper::hitungGajiPerJam(
                gajiPokokBulanan: $pegawai->jabatan->gaji,
                jumlahHariKerjaBulanan: 22,
                jamPerHari: 8
            );
        } else {
            // BTKL: Tarif per jam
            $gajiPerJam = $pegawai->jabatan->tarif;
        }

        // 4. Ambil komponen gaji
        $tunjangan = $pegawai->jabatan->tunjangan;
        $bonus = 0;  // Dari form atau database
        $potongan = 0;  // Dari form atau database

        // 5. Hitung total gaji
        $totalGaji = PresensiHelper::hitungTotalGaji(
            gajiPerJam: $gajiPerJam,
            totalJamKerja: $totalJamKerja,
            tunjangan: $tunjangan,
            bonus: $bonus,
            potongan: $potongan
        );

        // 6. Simpan atau update penggajian
        $penggajian = Penggajian::updateOrCreate(
            [
                'pegawai_id' => $pegawaiId,
                'tanggal_penggajian' => $tanggalMulai,
            ],
            [
                'total_jam_kerja' => $totalJamKerja,
                'gaji_per_jam' => $gajiPerJam,
                'tunjangan' => $tunjangan,
                'bonus' => $bonus,
                'potongan' => $potongan,
                'total_gaji' => $totalGaji,
            ]
        );

        return $penggajian;
    }

    /**
     * Tampilkan daftar penggajian dengan format rapi
     */
    public function index()
    {
        $penggajians = Penggajian::with('pegawai')
            ->orderBy('tanggal_penggajian', 'desc')
            ->paginate(20);

        return view('transaksi.penggajian.index', compact('penggajians'));
    }
}

// ============================================================================
// 3. CONTOH: Model Presensi dengan Mutator
// ============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\PresensiHelper;
use Carbon\Carbon;

class PresensiExample extends Model
{
    protected $table = 'presensis';

    protected $fillable = [
        'pegawai_id',
        'tgl_presensi',
        'jam_masuk',
        'jam_keluar',
        'status',
        'jumlah_menit_kerja',
        'jumlah_jam_kerja',
        'keterangan'
    ];

    protected $casts = [
        'tgl_presensi' => 'date',
        'jumlah_menit_kerja' => 'integer',
        'jumlah_jam_kerja' => 'decimal:1'
    ];

    /**
     * Accessor: Format jam kerja untuk tampilan
     * 
     * Penggunaan di Blade:
     * {{ $presensi->jam_kerja_formatted }}  // Output: "7,5 jam"
     */
    public function getJamKerjaFormattedAttribute(): string
    {
        return PresensiHelper::formatJamKerja($this->jumlah_jam_kerja);
    }

    /**
     * Mutator: Hitung durasi saat jam_masuk diubah
     */
    public function setJamMasukAttribute($value): void
    {
        $this->attributes['jam_masuk'] = $value;
        $this->hitungDurasiKerja();
    }

    /**
     * Mutator: Hitung durasi saat jam_keluar diubah
     */
    public function setJamKeluarAttribute($value): void
    {
        $this->attributes['jam_keluar'] = $value;
        $this->hitungDurasiKerja();
    }

    /**
     * Hitung durasi kerja otomatis
     */
    private function hitungDurasiKerja(): void
    {
        if ($this->jam_masuk && $this->jam_keluar) {
            try {
                $jamMasuk = Carbon::createFromFormat('H:i', $this->jam_masuk);
                $jamKeluar = Carbon::createFromFormat('H:i', $this->jam_keluar);

                if ($jamKeluar < $jamMasuk) {
                    $jamKeluar->addDay();
                }

                $durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);
                $this->attributes['jumlah_menit_kerja'] = $durasi['jumlah_menit_kerja'];
                $this->attributes['jumlah_jam_kerja'] = $durasi['jumlah_jam_kerja'];
            } catch (\Exception $e) {
                $this->attributes['jumlah_menit_kerja'] = 0;
                $this->attributes['jumlah_jam_kerja'] = 0;
            }
        }
    }
}

// ============================================================================
// 4. CONTOH: View Blade - Tabel Presensi
// ============================================================================

/*
File: resources/views/transaksi/presensi/index.blade.php

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
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
            @forelse($presensis as $presensi)
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
                    @switch($presensi->status)
                        @case('hadir')
                            <span class="badge bg-success">Hadir</span>
                            @break
                        @case('izin')
                            <span class="badge bg-warning">Izin</span>
                            @break
                        @case('sakit')
                            <span class="badge bg-danger">Sakit</span>
                            @break
                        @default
                            <span class="badge bg-secondary">{{ ucfirst($presensi->status) }}</span>
                    @endswitch
                </td>
                <td>
                    <a href="{{ route('transaksi.presensi.edit', $presensi->id) }}" 
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted">Tidak ada data presensi</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
*/

// ============================================================================
// 5. CONTOH: View Blade - Tabel Penggajian
// ============================================================================

/*
File: resources/views/transaksi/penggajian/index.blade.php

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
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
            @forelse($penggajians as $penggajian)
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
                    Rp {{ number_format($penggajian->gaji_per_jam * $penggajian->total_jam_kerja, 0, ',', '.') }}
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
                    <a href="{{ route('transaksi.penggajian.show', $penggajian->id) }}" 
                       class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i> Lihat
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center text-muted">Tidak ada data penggajian</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
*/

// ============================================================================
// 6. CONTOH: Unit Test
// ============================================================================

namespace Tests\Unit;

use App\Helpers\PresensiHelper;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PresensiHelperTest extends TestCase
{
    /**
     * Test: Hitung durasi kerja 7 jam 10 menit → 7 jam
     */
    public function test_durasi_kerja_7_jam_10_menit()
    {
        $jamMasuk = Carbon::createFromFormat('H:i', '07:00');
        $jamKeluar = Carbon::createFromFormat('H:i', '14:10');

        $durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);

        $this->assertEquals(430, $durasi['jumlah_menit_kerja']);
        $this->assertEquals(7.0, $durasi['jumlah_jam_kerja']);
    }

    /**
     * Test: Hitung durasi kerja 7 jam 20 menit → 7,5 jam
     */
    public function test_durasi_kerja_7_jam_20_menit()
    {
        $jamMasuk = Carbon::createFromFormat('H:i', '07:00');
        $jamKeluar = Carbon::createFromFormat('H:i', '14:20');

        $durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);

        $this->assertEquals(440, $durasi['jumlah_menit_kerja']);
        $this->assertEquals(7.5, $durasi['jumlah_jam_kerja']);
    }

    /**
     * Test: Hitung durasi kerja 7 jam 40 menit → 7,5 jam
     */
    public function test_durasi_kerja_7_jam_40_menit()
    {
        $jamMasuk = Carbon::createFromFormat('H:i', '07:00');
        $jamKeluar = Carbon::createFromFormat('H:i', '14:40');

        $durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);

        $this->assertEquals(460, $durasi['jumlah_menit_kerja']);
        $this->assertEquals(7.5, $durasi['jumlah_jam_kerja']);
    }

    /**
     * Test: Hitung durasi kerja 7 jam 50 menit → 8 jam
     */
    public function test_durasi_kerja_7_jam_50_menit()
    {
        $jamMasuk = Carbon::createFromFormat('H:i', '07:00');
        $jamKeluar = Carbon::createFromFormat('H:i', '14:50');

        $durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);

        $this->assertEquals(470, $durasi['jumlah_menit_kerja']);
        $this->assertEquals(8.0, $durasi['jumlah_jam_kerja']);
    }

    /**
     * Test: Format jam kerja
     */
    public function test_format_jam_kerja()
    {
        $this->assertEquals('7 jam', PresensiHelper::formatJamKerja(7));
        $this->assertEquals('7,5 jam', PresensiHelper::formatJamKerja(7.5));
        $this->assertEquals('8 jam', PresensiHelper::formatJamKerja(8));
    }

    /**
     * Test: Hitung gaji per jam
     */
    public function test_hitung_gaji_per_jam()
    {
        $gajiPerJam = PresensiHelper::hitungGajiPerJam(2200000);
        $this->assertEquals(12500, $gajiPerJam);
    }

    /**
     * Test: Hitung total gaji
     */
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
}

// ============================================================================
// 7. CATATAN IMPLEMENTASI
// ============================================================================

/*
LANGKAH-LANGKAH IMPLEMENTASI:

1. Copy PresensiHelper.php ke app/Helpers/
   - Pastikan namespace benar: App\Helpers\PresensiHelper

2. Update Model Presensi:
   - Tambahkan import: use App\Helpers\PresensiHelper;
   - Tambahkan kolom ke $fillable: jumlah_menit_kerja, jumlah_jam_kerja
   - Tambahkan mutator setJamMasukAttribute dan setJamKeluarAttribute
   - Tambahkan accessor getJamKerjaFormattedAttribute

3. Jalankan Migration:
   php artisan migrate

4. Update Controller Presensi:
   - Tidak perlu hitung manual durasi, mutator sudah otomatis
   - Cukup: $presensi = new Presensi($validated); $presensi->save();

5. Update View Presensi:
   - Gunakan {{ $presensi->jam_kerja_formatted }} untuk tampilan jam
   - Contoh: <span class="badge">{{ $presensi->jam_kerja_formatted }}</span>

6. Update Controller Penggajian:
   - Gunakan PresensiHelper::hitungGajiPerJam() untuk hitung gaji per jam
   - Gunakan PresensiHelper::hitungTotalGaji() untuk hitung total gaji
   - Query: Presensi::where(...)->sum('jumlah_jam_kerja') untuk total jam

7. Update View Penggajian:
   - Tampilkan total_jam_kerja dengan format: {{ number_format($penggajian->total_jam_kerja, 1, ',', '') }} jam
   - Tampilkan gaji dengan format: Rp {{ number_format($penggajian->total_gaji, 0, ',', '.') }}

8. Testing:
   - Jalankan unit test: php artisan test
   - Test berbagai durasi kerja untuk verifikasi pembulatan

TIPS:
- Selalu gunakan Carbon untuk parsing jam
- Pastikan format jam adalah H:i (24-jam)
- Gunakan number_format untuk tampilan rupiah dan jam
- Jangan lupa validasi jam_keluar > jam_masuk
*/
