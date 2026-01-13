<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;

    protected $table = 'perusahaan';

    protected $fillable = ['nama', 'alamat', 'email', 'telepon', 'kode'];

    public function kasirs()
    {
        return $this->hasMany(Kasir::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Generate kode perusahaan unik
     */
    public static function generateKode(): string
    {
        do {
            $kode = strtoupper(substr(md5(uniqid()), 0, 6));
        } while (self::where('kode', $kode)->exists());
        
        return $kode;
    }
}
