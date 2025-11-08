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
        'keterangan',
        'aktual',
        'is_active'
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'aktual' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Relasi ke model Coa
     */
    public function coa()
    {
        return $this->belongsTo(Coa::class, 'kode_akun', 'kode_akun');
    }

    protected $appends = ['sisa_budget', 'sisa_budget_formatted'];

    /**
     * Scope untuk filter akun beban aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
        $totalRealisasi = $this->aktual ?? 0;
        
        return $this->budget - $totalRealisasi;
    }

    /**
     * Cek apakah BOP memiliki budget yang sudah diatur
     *
     * @return bool
     */
    public function hasBudget()
    {
        return !is_null($this->budget) && $this->budget > 0;
    }
}
