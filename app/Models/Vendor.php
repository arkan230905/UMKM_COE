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
    
    protected $appends = ['nama'];

    public function pembelians()
    {
        return $this->hasMany(Pembelian::class);
    }
    
    /**
     * Accessor untuk backward compatibility
     * Agar bisa menggunakan $vendor->nama
     */
    public function getNamaAttribute()
    {
        return $this->nama_vendor;
    }
    
    /**
     * Accessor untuk telepon
     */
    public function getTeleponAttribute()
    {
        return $this->no_telp;
    }
}
