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
        'faktor', // Add faktor field if it exists
        'user_id',
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
     *
     * @return void
     */
    protected static function booted()
    {
        parent::booted();
        
        // Auto-assign user_id saat creating
        static::creating(function ($satuan) {
            if (empty($satuan->user_id) && auth()->check()) {
                $satuan->user_id = auth()->id();
            }
        });
        
        // Global scope untuk data isolation
        static::addGlobalScope('user', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }
}
