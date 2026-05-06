<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',  // CRITICAL: multi-tenant isolation
        'tanggal',
        'ref_type',
        'ref_id',
        'memo',
    ];

    /**
     * Boot method - auto-fill user_id untuk multi-tenant isolation
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }
    
    public function linesWithAccount()
    {
        return $this->hasMany(JournalLine::class)->with('account');
    }
}
