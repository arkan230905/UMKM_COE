<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaPokokProduksiBiayaBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'harga_pokok_produksi_biaya_bahan_baku';

    protected $fillable = [
        'user_id',
        'biaya_bahan_baku_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'biaya_bahan_baku_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function biayaBahanBaku()
    {
        return $this->belongsTo(BiayaBahanBaku::class);
    }

    // Keep old relationship name for backward compatibility
    public function bomJobBbb()
    {
        return $this->biayaBahanBaku();
    }

    public function scopeByUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
