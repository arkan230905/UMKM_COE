<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'nama_vendor',
        'kategori',
        'alamat',
        'no_telp',
        'email'
    ];

    public function pembelians()
    {
        return $this->hasMany(Pembelian::class);
    }
}
