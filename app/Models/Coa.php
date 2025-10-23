<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    protected $fillable = [
        'nama_vendor', 'alamat', 'no_telp', 'email',
    ];
}
