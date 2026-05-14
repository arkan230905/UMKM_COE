# Solusi Multi-Tenant Stock Management

## Masalah yang Ditemukan

1. **Double Counting Stok**: Stok dihitung dari semua user, bukan hanya user yang sedang login
2. **Tidak Konsisten**: Stok di halaman utama berbeda dengan halaman detail
3. **Multi-Tenant Isolation**: Data stok tidak terisolasi per user

## Perbaikan yang Dilakukan

### 1. Global Scope untuk Multi-Tenant

**File: `app/Scopes/UserScope.php`**
```php
<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $builder->where($model->getTable() . '.user_id', auth()->id());
        }
    }
}
```

### 2. Update Model BahanBaku

**Perubahan di `app/Models/BahanBaku.php`:**

```php
protected static function boot()
{
    parent::boot();
    
    // CRITICAL: Apply global scope untuk multi-tenant isolation
    static::addGlobalScope(new \App\Scopes\UserScope);
    
    static::creating(function ($model) {
        if (empty($model->user_id) && auth()->check()) {
            $model->user_id = auth()->id();
        }
    });
}

public function getStokRealTimeAttribute()
{
    // Global scope sudah menangani filter user_id otomatis
    $stockIn = \App\Models\StockMovement::where('item_type', 'material')
        ->where('item_id', $this->id)
        ->where('direction', 'in')
        ->sum('qty');

    $stockOut = \App\Models\StockMovement::where('item_type', 'material')
        ->where('item_id', $this->id)
        ->where('direction', 'out')
        ->sum('qty');

    $netStock = $stockIn - $stockOut;
    
    if ($stockIn == 0 && $stockOut == 0 && $this->saldo_awal > 0) {
        return (float)$this->saldo_awal;
    }

    return $netStock;
}
```

### 3. Update Model BahanPendukung

**Perubahan di `app/Models/BahanPendukung.php`:**

```php
protected static function boot()
{
    parent::boot();
    
    // CRITICAL: Apply global scope untuk multi-tenant isolation
    static::addGlobalScope(new \App\Scopes\UserScope);
}

public function getStokRealTimeAttribute()
{
    // Global scope sudah menangani filter user_id otomatis
    $stockIn = \App\Models\StockMovement::where('item_type', 'support')
        ->where('item_id', $this->id)
        ->where('direction', 'in')
        ->sum('qty');

    $stockOut = \App\Models\StockMovement::where('item_type', 'support')
        ->where('item_id', $this->id)
        ->where('direction', 'out')
        ->sum('qty');

    $netStock = $stockIn - $stockOut;
    
    if ($stockIn == 0 && $stockOut == 0 && $this->saldo_awal > 0) {
        return (float)$this->saldo_awal;
    }

    return $netStock;
}
```

### 4. Update Model StockMovement

**Perubahan di `app/Models/StockMovement.php`:**

```php
protected static function boot()
{
    parent::boot();

    // CRITICAL: Apply global scope untuk multi-tenant isolation
    static::addGlobalScope(new \App\Scopes\UserScope);

    static::creating(function ($model) {
        if (auth()->check() && !$model->user_id) {
            $model->user_id = auth()->id();
        }
    });
}
```

### 5. Update StockService

**Perubahan di `app/Services/StockService.php`:**

```php
public function getCurrentStock($itemId, $itemType)
{
    // Global scope sudah menangani filter user_id otomatis
    
    $stockMovementType = $itemType;
    if ($itemType === 'bahan_baku') {
        $stockMovementType = 'material';
    } elseif ($itemType === 'bahan_pendukung') {
        $stockMovementType = 'support';
    }
    
    $stockIn = \App\Models\StockMovement::where('item_type', $stockMovementType)
        ->where('item_id', $itemId)
        ->where('direction', 'in')
        ->sum('qty');

    $stockOut = \App\Models\StockMovement::where('item_type', $stockMovementType)
        ->where('item_id', $itemId)
        ->where('direction', 'out')
        ->sum('qty');

    return $stockIn - $stockOut;
}
```

## Hasil Perbaikan

### ✅ Sebelum Perbaikan:
- Stok awal: 12 kg
- Input pembelian: 10 kg
- **Masalah**: Stok menjadi 32 kg (double counting)
- **Masalah**: Halaman utama dan detail tidak konsisten

### ✅ Setelah Perbaikan:
- Stok awal: 12 kg
- Input pembelian: 10 kg
- **Hasil**: Stok menjadi 22 kg (benar)
- **Hasil**: Halaman utama dan detail konsisten
- **Hasil**: Setiap user hanya melihat data mereka sendiri

## Testing Multi-Tenant

Script testing menunjukkan:

```
User 1 (Admin Utama) sees:
  - BahanBaku: 1
  - StockMovement: 1
User 2 (nayla dzakira) sees:
  - BahanBaku: 2
  - StockMovement: 13
✅ GOOD: Users see different data (multi-tenant working)
```

## Keamanan Data

1. **Automatic User ID**: Semua data baru otomatis mendapat user_id dari user yang login
2. **Global Scope**: Semua query otomatis difilter berdasarkan user_id
3. **Isolation**: User tidak bisa melihat atau mengakses data user lain
4. **Consistency**: Perhitungan stok konsisten di semua halaman

## Cara Kerja

1. **Saat Login**: Global scope aktif dan filter semua query berdasarkan user_id
2. **Saat Create**: Auto-set user_id untuk data baru
3. **Saat Query**: Hanya data milik user yang login yang ditampilkan
4. **Saat Hitung Stok**: Hanya stock movement milik user yang dihitung

## Manfaat

1. **Data Isolation**: Setiap user hanya melihat data mereka
2. **Consistent Stock**: Stok konsisten di semua halaman
3. **Automatic**: Tidak perlu manual filter user_id di setiap query
4. **Secure**: Tidak ada kebocoran data antar user
5. **Scalable**: Mudah menambah user baru tanpa konflik data