<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CoaPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    /**
     * Relasi ke saldo periode
     */
    public function balances()
    {
        return $this->hasMany(CoaPeriodBalance::class, 'period_id');
    }

    /**
     * Relasi ke user yang menutup periode
     */
    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get atau create periode berdasarkan tanggal
     */
    public static function getOrCreatePeriod($date)
    {
        $carbon = Carbon::parse($date);
        $periode = $carbon->format('Y-m');
        
        return self::firstOrCreate(
            ['periode' => $periode],
            [
                'tanggal_mulai' => $carbon->startOfMonth()->toDateString(),
                'tanggal_selesai' => $carbon->endOfMonth()->toDateString(),
            ]
        );
    }

    /**
     * Get periode aktif (bulan ini)
     */
    public static function getCurrentPeriod()
    {
        return self::getOrCreatePeriod(now());
    }

    /**
     * Get periode sebelumnya
     */
    public function getPreviousPeriod()
    {
        $prevMonth = Carbon::parse($this->tanggal_mulai)->subMonth();
        return self::where('periode', $prevMonth->format('Y-m'))->first();
    }

    /**
     * Get periode berikutnya
     */
    public function getNextPeriod()
    {
        $nextMonth = Carbon::parse($this->tanggal_mulai)->addMonth();
        return self::where('periode', $nextMonth->format('Y-m'))->first();
    }
}
