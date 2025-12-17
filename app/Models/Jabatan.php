<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    protected $fillable = ['kode_jabatan', 'nama', 'kategori', 'tunjangan', 'asuransi', 'tarif', 'gaji'];

    protected $casts = [
        'tunjangan' => 'decimal:2',
        'asuransi' => 'decimal:2',
        'tarif' => 'decimal:2',
        'gaji' => 'decimal:2',
    ];

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }

    public function tunjangans(): HasMany
    {
        return $this->hasMany(KlasifikasiTunjangan::class);
    }

    public function tunjanganTambahans(): HasMany
    {
        return $this->hasMany(JabatanTunjanganTambahan::class);
    }
}
