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
        'faktor',
    ];

    /**
     * Get the bahan bakus for the satuan.
     */
    public function bahanBakus()
    {
        return $this->hasMany(BahanBaku::class, 'satuan_id');
    }

    /**
     * Get the bahan pendukungs for the satuan.
     */
    public function bahanPendukungs()
    {
        return $this->hasMany(BahanPendukung::class, 'satuan_id');
    }

    /**
     * Get the user that owns the satuan
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        parent::booted();
        // user_id column does not exist on satuans table — no scope needed
    }
}
