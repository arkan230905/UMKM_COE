<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    // Relasi ke order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke produk melalui order items
    public function produk()
    {
        return $this->hasOneThrough(
            Produk::class,
            OrderItem::class,
            'order_id', // Foreign key di order_items
            'id', // Foreign key di produk
            'order_id', // Local key di reviews
            'produk_id' // Local key di order_items
        );
    }

    // Scope untuk rating tertentu
    public function scopeRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }
}
