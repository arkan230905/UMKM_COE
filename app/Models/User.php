<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_OWNER = 'owner';
    const ROLE_PELANGGAN = 'pelanggan';
    const ROLE_PEGAWAI_PEMBELIAN = 'pegawai_pembelian';
    
    const VALID_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_OWNER,
        self::ROLE_PELANGGAN,
        self::ROLE_PEGAWAI_PEMBELIAN,
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'role',
        'perusahaan_id',
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
     * Get reviews by this user
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
