<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_bakus'; // <--- PENTING: samakan dengan nama tabel di migration
    // Nonaktifkan sementara mass assignment protection untuk testing
    protected $guarded = [];
    
    protected $casts = [
        'harga_satuan' => 'float',
        'stok' => 'float',
    ];
    
    /**
     * Set the harga_satuan attribute.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setHargaSatuanAttribute($value)
    {
        $this->attributes['harga_satuan'] = (float)$value;
    }

    /**
     * Get the satuan that owns the BahanBaku
     */
    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }
}
