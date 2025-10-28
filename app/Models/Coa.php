<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Bop;

class Coa extends Model
{
    use HasFactory;

    protected $table = 'coas';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe_akun',
    ];

    // Relasi ke BOP
    public function bop()
    {
        return $this->hasMany(Bop::class, 'coa_id');
    }

    // Event otomatis membuat data BOP jika tipe akun sesuai
    protected static function booted()
    {
        static::created(function ($coa) {
            // Ubah sesuai tipe akun yang kamu gunakan
            if (in_array(strtolower($coa->tipe_akun), ['expense', 'beban', 'biaya'])) {
                Bop::create([
                    'coa_id' => $coa->id,
                    'keterangan' => 'Otomatis dari COA',
                    'nominal' => null,
                    'tanggal' => null,
                ]);
            }
        });
    }
}
