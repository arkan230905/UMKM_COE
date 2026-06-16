<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJobBopSelection extends Model
{
    use HasFactory;

    protected $table = 'bom_job_bop_selections';

    protected $fillable = [
        'user_id', 'bom_job_costing_id', 'bop_proses_id',
        'tarif', 'jumlah', 'subtotal', 'keterangan'
    ];

    protected $casts = [
        'tarif' => 'decimal:2',
        'jumlah' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bomJobCosting()
    {
        return $this->belongsTo(BomJobCosting::class);
    }

    public function bopProses()
    {
        return $this->belongsTo(BopProses::class);
    }

    public function scopeByUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
