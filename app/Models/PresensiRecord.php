<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiRecord extends Model
{
    use \App\Traits\HasUserScope;
    protected $table = 'presensi_records';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_id',  // CRITICAL: multi-tenant isolation
        'presensi_user_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status_masuk',
        'status_keluar',
        'keterangan',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_keluar',
        'longitude_keluar',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'tanggal',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the user that owns this record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the presensi user that owns this record
     */
    public function presensiUser(): BelongsTo
    {
        return $this->belongsTo(PresensiUser::class, 'presensi_user_id');
    }

    /**
     * Scope to get records for current user
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
