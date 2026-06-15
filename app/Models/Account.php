<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use \App\Traits\HasUserScope;
    use HasFactory;

    protected $table = 'accounts';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe_akun',
        'kategori_akun',
        'saldo_normal',
        'keterangan',
        'saldo_awal',
        'tanggal_saldo_awal',
        'posted_saldo_awal',
        'company_id',
        'nomor_rekening',
        'atas_nama',
        'user_id',
        'kode_induk',
        'is_akun_header',
    ];

    protected $casts = [
        'posted_saldo_awal' => 'boolean',
        'saldo_awal' => 'decimal:2',
        'tanggal_saldo_awal' => 'date',
        'is_akun_header' => 'boolean',
    ];
}
