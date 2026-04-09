<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Pembelian;
use App\Models\Coa;
use App\Models\User;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class PelunasanUtang extends Model
{
    protected $table = 'pelunasan_utangs';
    
    protected $fillable = [
        'kode_transaksi',
        'pembelian_id',
        'tanggal',
        'akun_kas_id',
        'coa_pelunasan_id',
        'jumlah',
        'keterangan',
        'status',
        'user_id',
        'catatan'
    ];

    protected $dates = ['tanggal', 'deleted_at'];
    
    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }
    
    public function akunKas()
    {
        return $this->belongsTo(Coa::class, 'akun_kas_id');
    }
    
    public function coaPelunasan()
    {
        return $this->belongsTo(Coa::class, 'coa_pelunasan_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function jurnals()
    {
        return $this->hasMany(JournalLine::class, 'journal_entry_id', 'id')
            ->whereHas('entry', function($query) {
                $query->where('ref_type', 'debt_payment')
                      ->where('ref_id', $this->id);
            });
    }
    
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'lunas' => '<span class="badge badge-success">Lunas</span>',
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'batal' => '<span class="badge badge-danger">Batal</span>',
        ];
        
        return $badges[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }
}
