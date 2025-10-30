<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal','coa_beban_id','metode_bayar','coa_kasbank','nominal','deskripsi','user_id'
    ];

    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_beban_id');
    }
}
