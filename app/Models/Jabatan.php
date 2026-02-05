<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    protected $fillable = ['kode_jabatan', 'nama', 'kategori', 'tunjangan', 'asuransi', 'tarif', 'gaji', 'deskripsi'];

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'jabatan', 'nama');
    }

    /**
     * Get count of active employees for this position
     */
    public function getJumlahPegawaiAttribute()
    {
        return $this->pegawais()->count();
    }
    
    /**
     * Calculate automatic BTKL rate based on tariff and employee count
     */
    public function getTarifBtklAttribute()
    {
        $jumlahPegawai = $this->pegawais()->count();
        return $this->tarif * $jumlahPegawai;
    }
}
