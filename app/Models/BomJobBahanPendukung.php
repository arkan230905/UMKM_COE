<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\UnitConverter;

class BomJobBahanPendukung extends Model
{
    use HasFactory;
    protected $table = 'bom_job_bahan_pendukung';
    protected $fillable = ['bom_job_costing_id', 'bahan_pendukung_id', 'jumlah', 'satuan', 'harga_satuan', 'subtotal', 'keterangan'];
    protected $casts = ['jumlah' => 'decimal:4', 'harga_satuan' => 'decimal:2', 'subtotal' => 'decimal:2'];

    protected static function booted()
    {
        static::saving(function ($m) {
            $jumlah = (float)($m->jumlah ?? 0);
            $harga = (float)($m->harga_satuan ?? 0);

            if ($jumlah <= 0 || $harga <= 0) {
                $m->subtotal = 0;
                return;
            }

            $qtyBase = $jumlah;
            if ($m->bahanPendukung) {
                $converter = new UnitConverter();
                $satuanBaseObj = $m->bahanPendukung->satuan;
                $satuanBase = is_object($satuanBaseObj) ? ($satuanBaseObj->nama ?? 'unit') : ($satuanBaseObj ?: 'unit');
                $satuanInput = (string)($m->satuan ?: $satuanBase);

                $desc = $converter->describe($satuanInput, $satuanBase);
                if ($desc !== 'konversi tidak dikenal' && !str_contains($desc, 'volume↔massa')) {
                    $qtyBase = $converter->convert($jumlah, $satuanInput, $satuanBase);
                }
            }

            $m->subtotal = $qtyBase * $harga;
        });
        static::saved(function ($m) { $m->bomJobCosting?->recalculate(); });
        static::deleted(function ($m) { $m->bomJobCosting?->recalculate(); });
    }

    public function bomJobCosting() { return $this->belongsTo(BomJobCosting::class, 'bom_job_costing_id'); }
    public function bahanPendukung() { return $this->belongsTo(BahanPendukung::class, 'bahan_pendukung_id'); }
}
