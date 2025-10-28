<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type','item_id','tanggal','remaining_qty','unit_cost','satuan','ref_type','ref_id'
    ];
}
