<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bop extends Model
{
    use HasFactory;

    protected $table = 'bops';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'jumlah',
        'harga_satuan',
        'total',
        'keterangan',
        'tanggal',
        'coa_id',
        'budget',
        'is_active',
        'nominal',
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];

    // Relasi ke COA
    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id', 'id');
    }
    
    // Scope untuk BOP aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Hitung sisa budget
    public function getSisaBudgetAttribute()
    {
        return $this->budget - $this->nominal;
    }
}
