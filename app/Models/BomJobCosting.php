<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJobCosting extends Model
{
    use HasFactory;

    protected $table = 'bom_job_costings';

    protected $fillable = [
        'user_id', 'produk_id', 'kode_hpp',
        'total_bbb', 'total_btkl', 'total_bop', 'total_hpp',
        'keterangan'
    ];

    protected $casts = [
        'total_bbb' => 'decimal:2',
        'total_btkl' => 'decimal:2',
        'total_bop' => 'decimal:2',
        'total_hpp' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function bbbSelections()
    {
        return $this->hasMany(BomJobBbbSelection::class);
    }

    public function btklSelections()
    {
        return $this->hasMany(BomJobBtklSelection::class);
    }

    public function bopSelections()
    {
        return $this->hasMany(BomJobBopSelection::class);
    }

    public function scopeByUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public static function generateKodeHpp()
    {
        $prefix = 'HPP-';
        $date = date('Ymd');
        $lastRecord = self::where('kode_hpp', 'like', $prefix . $date . '%')
            ->orderBy('kode_hpp', 'desc')
            ->first();
        
        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->kode_hpp, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Recalculate all totals from detail tables
     * This method aggregates data from:
     * - bom_job_bbb (sum subtotal)
     * - bom_job_btkl (sum subtotal)
     * - bom_job_bahan_pendukung (sum subtotal)
     * - bom_job_bop_selections (sum subtotal)
     */
    public function recalculate()
    {
        try {
            // Calculate BBB total
            $totalBBB = \DB::table('bom_job_bbb')
                ->where('bom_job_costing_id', $this->id)
                ->sum('subtotal');
            
            // Calculate BTKL total
            $totalBTKL = \DB::table('bom_job_btkl')
                ->where('bom_job_costing_id', $this->id)
                ->sum('subtotal');
            
            // Calculate Bahan Pendukung total
            $totalBahanPendukung = \DB::table('bom_job_bahan_pendukung')
                ->where('bom_job_costing_id', $this->id)
                ->sum('subtotal');
            
            // Calculate BOP total from bom_job_bop_selections
            $totalBOP = \DB::table('bom_job_bop_selections')
                ->where('bom_job_costing_id', $this->id)
                ->sum('subtotal');
            
            // Calculate total HPP
            $totalHPP = $totalBBB + $totalBTKL + $totalBahanPendukung + $totalBOP;
            
            // Calculate HPP per unit
            $hppPerUnit = $this->jumlah_produk > 0 ? $totalHPP / $this->jumlah_produk : 0;
            
            // Update this model (use saveQuietly to prevent infinite recursion)
            $this->fill([
                'total_bbb' => $totalBBB,
                'total_btkl' => $totalBTKL,
                'total_bahan_pendukung' => $totalBahanPendukung,
                'total_bop' => $totalBOP,
                'total_hpp' => $totalHPP,
                'hpp_per_unit' => $hppPerUnit,
            ])->saveQuietly();
            
            \Log::info('BomJobCosting recalculated', [
                'id' => $this->id,
                'total_bbb' => $totalBBB,
                'total_btkl' => $totalBTKL,
                'total_bahan_pendukung' => $totalBahanPendukung,
                'total_bop' => $totalBOP,
                'total_hpp' => $totalHPP,
                'hpp_per_unit' => $hppPerUnit,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('BomJobCosting recalculate failed', [
                'id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return $this;
    }
}
