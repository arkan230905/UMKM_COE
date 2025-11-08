<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Bop extends Model
{
    use HasFactory;

    protected $table = 'bops';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'budget',
        'aktual',
        'is_active'
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'aktual' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    protected $appends = ['sisa_budget', 'sisa_budget_formatted'];

    /**
     * Scope untuk filter akun beban aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Relasi ke COA
     */
    public function coa()
    {
        return $this->belongsTo(Coa::class, 'kode_akun', 'kode_akun');
    }

    /**
     * Format sisa budget
     *
     * @return string
     */
    public function getSisaBudgetFormattedAttribute()
    {
        return number_format($this->sisa_budget, 2, ',', '.');
    }
    
    /**
     * Hitung sisa budget
     *
     * @return float
     */
    public function getSisaBudgetAttribute()
    {
        // Hitung total realisasi dari transaksi terkait
        $totalRealisasi = 0; // Ganti dengan logika perhitungan realisasi
        
        return $this->budget - $totalRealisasi;
    }

    /**
     * Cek apakah budget sudah diisi
     *
     * @return bool
     */
    public function hasBudget()
    {
        return $this->budget > 0;
    }
}
