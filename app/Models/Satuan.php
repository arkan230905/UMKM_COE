<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;

    // Pastikan sesuai nama tabel di DB
    protected $table = 'satuans';

    // Kolom yang boleh di-mass assign
    protected $fillable = [
        'kode',
        'nama',
    ];
}
