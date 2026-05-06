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
}
