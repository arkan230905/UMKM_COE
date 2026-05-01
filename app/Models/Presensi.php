<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Presensi extends Model
{
    protected $table = 'presensis';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'pegawai_id',
        'tgl_presensi',
        'periode_bulan',
        'periode_tahun',
        'jam_masuk',
        'jam_keluar',
        'status',
        'jumlah_jam',
        'keterangan',
        'verifikasi_wajah',
        'foto_wajah',
        'waktu_verifikasi',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_keluar',
        'longitude_keluar'
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-fill periode_bulan dan periode_tahun saat creating
        static::creating(function ($presensi) {
            if ($presensi->tgl_presensi) {
                $date = Carbon::parse($presensi->tgl_presensi);
                $presensi->periode_bulan = $date->month;
                $presensi->periode_tahun = $date->year;
            }
        });

        // Auto-calculate jumlah_jam saat creating/updating
        static::saving(function ($presensi) {
            // Update periode if tgl_presensi changed
            if ($presensi->isDirty('tgl_presensi') && $presensi->tgl_presensi) {
                $date = Carbon::parse($presensi->tgl_presensi);
                $presensi->periode_bulan = $date->month;
                $presensi->periode_tahun = $date->year;
            }

            // Calculate jumlah_jam if both jam_masuk and jam_keluar exist
            if ($presensi->jam_masuk && $presensi->jam_keluar) {
                $jumlahJam = $presensi->hitungJumlahJam();
                if ($jumlahJam !== null) {
                    $presensi->jumlah_jam = $jumlahJam;
                }
            } else {
                $presensi->jumlah_jam = 0;
            }

            // Auto-set status based on attendance
            if (!$presensi->status || $presensi->isDirty(['jam_masuk', 'jam_keluar'])) {
                if ($presensi->jam_masuk && $presensi->jam_keluar) {
                    $presensi->status = 'Hadir';
                } elseif ($presensi->jam_masuk && !$presensi->jam_keluar) {
                    $presensi->status = 'Masuk Saja';
                } else {
                    $presensi->status = 'Alpha';
                }
            }
        });
    }

    protected $casts = [
        'tgl_presensi' => 'date',
        'jam_masuk' => 'string',
        'jam_keluar' => 'string',
        'verifikasi_wajah' => 'boolean',
        'waktu_verifikasi' => 'datetime'
    ];

    protected $dates = [
        'tgl_presensi',
        'created_at',
        'updated_at'
    ];

    /**
     * Hitung jumlah jam kerja (method biasa, bukan accessor)
     */
    public function hitungJumlahJam()
    {
        // Jika tidak ada jam masuk atau keluar, return null
        if (!$this->jam_masuk || !$this->jam_keluar) {
            return null;
        }

        try {
            $tanggal = $this->tgl_presensi 
                ? Carbon::parse($this->tgl_presensi)->format('Y-m-d')
                : Carbon::today()->format('Y-m-d');
            
            $start = Carbon::parse($tanggal . ' ' . $this->jam_masuk);
            $end = Carbon::parse($tanggal . ' ' . $this->jam_keluar);
            
            // Jika jam keluar lebih kecil atau sama dengan jam masuk, berarti lintas hari
            if ($end->lte($start)) {
                $end->addDay();
            }

            // Hitung selisih dalam menit (pastikan positif)
            $minutes = abs($start->diffInMinutes($end, false));
            $hours = (int) floor($minutes / 60); // bulat ke bawah

            return $hours;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Accessor untuk menghitung jumlah jam otomatis (bulat ke bawah)
     */
    public function getJumlahJamAttribute($value)
    {
        // Jika sudah ada nilai di database, gunakan itu
        if ($value !== null && $value != 0) {
            return (int) $value;
        }

        // Gunakan method hitungJumlahJam
        $calculated = $this->hitungJumlahJam();
        
        return $calculated ?? 0;
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    /**
     * Get nama bulan
     */
    public function getNamaBulanAttribute()
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $this->periode_bulan ? ($namaBulan[$this->periode_bulan] ?? '') : '';
    }

    /**
     * Get periode label
     */
    public function getPeriodeLabelAttribute()
    {
        return $this->nama_bulan . ' ' . $this->periode_tahun;
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('pegawai', function($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('kode_pegawai', 'like', "%{$search}%");
        })
        ->orWhere('status', 'like', "%{$search}%")
        ->orWhereDate('tgl_presensi', $search);
    }

    /**
     * Scope untuk filter by periode
     */
    public function scopeByPeriode($query, $bulan, $tahun)
    {
        return $query->where('periode_bulan', $bulan)
                     ->where('periode_tahun', $tahun);
    }

    /**
     * Scope untuk filter by pegawai
     */
    public function scopeByPegawai($query, $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }
}