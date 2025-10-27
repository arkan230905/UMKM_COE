<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    use HasFactory;

    // Pastikan tabel yang digunakan benar
    protected $table = 'coas';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe_akun',
    ];

    // 🔽 Relasi ke Bop
    public function bop()
    {
        return $this->hasMany(Bop::class, 'coa_id');
    }

    // 🔽 Event otomatis membuat data BOP
    protected static function booted()
    {
        static::created(function ($coa) {
            if ($coa->tipe_akun === 'Expense') {
                \App\Models\Bop::create([
                    'coa_id' => $coa->id,
                    'keterangan' => 'Otomatis dari COA',
                ]);
            }
        });
    }
}
