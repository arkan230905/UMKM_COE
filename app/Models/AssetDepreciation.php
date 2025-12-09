<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'tahun',
        'beban_penyusutan',
        'akumulasi_penyusutan',
        'nilai_buku_akhir',
    ];

    protected $casts = [
        'beban_penyusutan' => 'decimal:2',
        'akumulasi_penyusutan' => 'decimal:2',
        'nilai_buku_akhir' => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'asset_id');
    }
}
