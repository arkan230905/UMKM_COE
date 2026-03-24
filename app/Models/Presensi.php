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
}