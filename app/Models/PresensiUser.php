<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiUser extends Model
{
    protected $table = 'presensi_users';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'user_id',  // CRITICAL: multi-tenant isolation
        'nama_lengkap',
        'nik',
        'jabatan',
        'email',
        'kode_perusahaan',
        'is_active',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the user that owns this presensi user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get records for current user
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
