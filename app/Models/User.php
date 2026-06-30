<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_OWNER = 'owner';
    const ROLE_PEGAWAI = 'pegawai';
    const ROLE_PELANGGAN = 'pelanggan';
    const ROLE_PEGAWAI_PEMBELIAN = 'pegawai_pembelian';
    const ROLE_KASIR = 'kasir';
    
    const VALID_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_OWNER,
        self::ROLE_PEGAWAI,
        self::ROLE_PELANGGAN,
        self::ROLE_PEGAWAI_PEMBELIAN,
        self::ROLE_KASIR,
    ];

    protected $fillable = [
        'pegawai_id',
        'name',
        'username',
        'email',
        'phone',
        'address',
        'password',
        'plain_password', // CRITICAL: Add plain_password for display purposes
        'role',
        'perusahaan_id',
        'profile_photo',
        'store_latitude',
        'store_longitude',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is an owner
     */
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    /**
     * Relasi ke perusahaan (tenant)
     */
    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    /**
     * Check if user is a pelanggan (customer)
     */
    public function isPelanggan(): bool
    {
        return $this->role === self::ROLE_PELANGGAN;
    }

    /**
     * Check if user is a pegawai pembelian (purchasing staff)
     */
    public function isPegawaiPembelian(): bool
    {
        return $this->role === self::ROLE_PEGAWAI_PEMBELIAN;
    }

    /**
     * Check if user is a kasir (cashier)
     */
    public function isKasir(): bool
    {
        return $this->role === self::ROLE_KASIR;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Get orders for this user (pelanggan)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get cart items for this user
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get favorites for this user
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get customer addresses for this user
     */
    public function customerAddresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
