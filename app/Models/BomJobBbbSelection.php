<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomJobBbbSelection extends Model
{
    use \App\Traits\HasUserScope;
    use HasFactory;

    protected $table = 'bom_job_bbb_selections';

    protected $fillable = [
        'user_id', 'bom_job_costing_id', 'bom_job_bbb_id',
        'jumlah', 'harga_satuan', 'subtotal'
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
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

    public function bomJobBbb()
    {
        return $this->belongsTo(BomJobBbb::class);
    }

    public function scopeByUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
