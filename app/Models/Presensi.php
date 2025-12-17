<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Helpers\PresensiHelper;
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
        'jumlah_menit_kerja',
        'jumlah_jam_kerja',
        'keterangan'
    ];

    protected $casts = [
        'tgl_presensi' => 'date',
        'jam_masuk' => 'string',
        'jam_keluar' => 'string',
        'jumlah_jam' => 'decimal:2',
        'jumlah_menit_kerja' => 'integer',
        'jumlah_jam_kerja' => 'decimal:1'
    ];

    protected $attributes = [
        'jumlah_menit_kerja' => 0,
        'jumlah_jam_kerja' => 0,
    ];

    protected $dates = [
        'tgl_presensi',
        'created_at',
        'updated_at'
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    /**
     * Accessor: Format jam kerja untuk tampilan (misal: 7, 7.5, 8 jam)
     */
    public function getJamKerjaFormattedAttribute(): string
    {
        return PresensiHelper::formatJamKerja($this->jumlah_jam_kerja);
    }

    /**
     * Mutator: Hitung durasi kerja saat jam_masuk atau jam_keluar diubah
     */
    public function setJamMasukAttribute($value): void
    {
        $this->attributes['jam_masuk'] = $value;
        $this->hitungDurasiKerja();
    }

    /**
     * Mutator: Hitung durasi kerja saat jam_keluar diubah
     */
    public function setJamKeluarAttribute($value): void
    {
        $this->attributes['jam_keluar'] = $value;
        $this->hitungDurasiKerja();
    }

    /**
     * Hitung durasi kerja dari jam_masuk dan jam_keluar
     */
    private function hitungDurasiKerja(): void
    {
        if ($this->jam_masuk && $this->jam_keluar) {
            try {
                // Parse jam masuk dan jam keluar
                $jamMasuk = Carbon::createFromFormat('H:i', $this->jam_masuk);
                $jamKeluar = Carbon::createFromFormat('H:i', $this->jam_keluar);

                // Jika jam keluar lebih kecil dari jam masuk, anggap hari berikutnya
                if ($jamKeluar < $jamMasuk) {
                    $jamKeluar->addDay();
                }

                // Hitung durasi kerja
                $durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);

                $this->attributes['jumlah_menit_kerja'] = $durasi['jumlah_menit_kerja'];
                $this->attributes['jumlah_jam_kerja'] = $durasi['jumlah_jam_kerja'];
            } catch (\Exception $e) {
                // Jika ada error parsing, set nilai default
                $this->attributes['jumlah_menit_kerja'] = 0;
                $this->attributes['jumlah_jam_kerja'] = 0;
            }
        }
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('pegawai', function($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nomor_induk_pegawai', 'like', "%{$search}%");
        })
        ->orWhere('status', 'like', "%{$search}%")
        ->orWhereDate('tgl_presensi', $search);
    }
}