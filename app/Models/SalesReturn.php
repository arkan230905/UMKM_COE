<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends Model
{
    protected $fillable = [
        'return_number',
        'penjualan_id',
        'return_date',
        'reason',
        'notes',
        'total_return_amount',
        'status',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_return_amount' => 'decimal:2',
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($return) {
            if (empty($return->return_number)) {
                $date = now()->format('Ymd');
                
                // Generate unique return number dengan retry mechanism
                $maxRetries = 10;
                $attempt = 0;
                
                do {
                    // Cari nomor terakhir untuk tanggal ini
                    $lastNumber = static::whereDate('created_at', today())
                        ->where('return_number', 'like', 'SRTN-' . $date . '-%')
                        ->orderBy('return_number', 'desc')
                        ->value('return_number');
                    
                    if ($lastNumber) {
                        // Extract nomor urut dari nomor terakhir
                        $parts = explode('-', $lastNumber);
                        $lastSequence = intval(end($parts));
                        $nextSequence = $lastSequence + 1;
                    } else {
                        $nextSequence = 1;
                    }
                    
                    // Format: SRTN-YYYYMMDD-0001
                    $returnNumber = 'SRTN-' . $date . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
                    
                    // Cek apakah nomor sudah ada (untuk memastikan unique)
                    $exists = static::where('return_number', $returnNumber)->exists();
                    
                    if (!$exists) {
                        $return->return_number = $returnNumber;
                        break;
                    }
                    
                    $attempt++;
                    
                    // Jika sudah mencoba berkali-kali, tambahkan timestamp untuk memastikan unique
                    if ($attempt >= $maxRetries) {
                        $timestamp = now()->format('His'); // HHMMSS
                        $return->return_number = 'SRTN-' . $date . '-' . $timestamp;
                        break;
                    }
                    
                } while ($attempt < $maxRetries);
            }
        });
    }
}
