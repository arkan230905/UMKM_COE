<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    protected $fillable = ['kode_jabatan', 'nama', 'kategori', 'tunjangan', 'asuransi', 'tarif_lembur', 'gaji_pokok', 'deskripsi'];

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }
}
