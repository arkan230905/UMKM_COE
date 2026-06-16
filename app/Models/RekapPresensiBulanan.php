<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapPresensiBulanan extends Model
{
    use \App\Traits\HasUserScope;
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'rekap_presensi_bulanan';

    // Menambahkan user_id ke fillable agar bisa disimpan berdasarkan pemilik data
    protected $fillable = [
        'user_id',
        'pegawai_id',
        'periode_bulan',
        'periode_tahun',
        'total_hari_hadir',
        'total_alpha',
        'total_masuk_saja',
        'total_jam_bulanan',
        'target_hari_kerja',
        'persentase_kehadiran',
        'estimasi_gaji',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'total_hari_hadir' => 'integer',
        'total_alpha' => 'integer',
        'total_masuk_saja' => 'integer',
        'total_jam_bulanan' => 'decimal:2',
        'target_hari_kerja' => 'integer',
        'persentase_kehadiran' => 'decimal:2',
        'estimasi_gaji' => 'decimal:2',
    ];

    /**
     * Relasi ke User (Pemilik data/Owner)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Pegawai
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    /**
     * Get nama bulan (Accessor)
     */
    public function getNamaBulanAttribute()
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $namaBulan[$this->periode_bulan] ?? '';
    }

    /**
     * Get periode label (Accessor)
     */
    public function getPeriodeLabelAttribute()
    {
        return $this->nama_bulan . ' ' . $this->periode_tahun;
    }

    /**
     * Generate atau update rekap untuk pegawai dan periode tertentu
     */
    public static function generateRekap($pegawaiId, $bulan, $tahun)
    {
        // Mendapatkan user_id dari user yang sedang login agar data terikat ke owner
        $currentUserId = auth()->id();

        // Ambil semua presensi untuk periode ini
        $presensiList = Presensi::where('pegawai_id', $pegawaiId)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->get();

        // Hitung total
        $totalHariHadir = $presensiList->where('status', 'Hadir')->count();
        $totalAlpha = $presensiList->where('status', 'Alpha')->count();
        $totalMasukSaja = $presensiList->where('status', 'Masuk Saja')->count();
        $totalJamBulanan = $presensiList->sum('jumlah_jam');

        // Ambil target hari kerja (Asumsi model KalenderKerja tersedia)
        $targetHariKerja = class_exists(KalenderKerja::class) 
            ? KalenderKerja::getTargetHariKerja($bulan, $tahun) 
            : 20;

        // Hitung persentase kehadiran
        $persentaseKehadiran = $targetHariKerja > 0 
            ? ($totalHariHadir / $targetHariKerja) * 100 
            : 0;

        // Ambil data pegawai untuk menghitung tarif gaji
        $pegawai = Pegawai::find($pegawaiId);
        $tarifPerJam = ($pegawai && $pegawai->jabatan) 
            ? $pegawai->jabatan->tarif_btkl 
            : 0;

        // Hitung estimasi gaji
        $estimasiGaji = $totalJamBulanan * $tarifPerJam;

        // Update atau create rekap dengan menyertakan user_id
        return static::updateOrCreate(
            [
                'user_id' => $currentUserId,
                'pegawai_id' => $pegawaiId,
                'periode_bulan' => $bulan,
                'periode_tahun' => $tahun,
            ],
            [
                'total_hari_hadir' => $totalHariHadir,
                'total_alpha' => $totalAlpha,
                'total_masuk_saja' => $totalMasukSaja,
                'total_jam_bulanan' => $totalJamBulanan,
                'target_hari_kerja' => $targetHariKerja,
                'persentase_kehadiran' => $persentaseKehadiran,
                'estimasi_gaji' => $estimasiGaji,
            ]
        );
    }

    /**
     * Generate rekap untuk semua pegawai aktif di periode tertentu
     */
    public static function generateRekapBulanan($bulan, $tahun)
    {
        // Mengambil pegawai yang milik user yang sedang login
        $pegawaiList = Pegawai::where('user_id', auth()->id())->get();
        $results = [];

        foreach ($pegawaiList as $pegawai) {
            $results[] = static::generateRekap($pegawai->id, $bulan, $tahun);
        }

        return $results;
    }
}