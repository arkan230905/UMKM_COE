# ✅ FIX: Error Column 'user_id' Not Found di Retur Penjualan

**Date**: May 6, 2026  
**Status**: 🔧 READY TO FIX

---

## 🎯 Problem

Error saat membuka halaman `/transaksi/penjualan`:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'user_id' in 'where clause'
SQL: select * from `retur_penjualans` where `user_id` = 1 order by `created_at` desc
```

**Root Cause**: 
- Tabel `retur_penjualans` tidak memiliki kolom `user_id`
- Kode di `PenjualanController.php` line 86 mencoba filter by `user_id` untuk multi-tenant isolation

---

## ✅ Solusi

### Step 1: Jalankan Migration

Saya telah membuat migration file untuk menambahkan kolom `user_id`:

**File**: `database/migrations/2026_05_06_000001_add_user_id_to_retur_penjualans_table.php`

**Jalankan command**:
```bash
php artisan migrate
```

**Output yang diharapkan**:
```
Migrating: 2026_05_06_000001_add_user_id_to_retur_penjualans_table
Migrated:  2026_05_06_000001_add_user_id_to_retur_penjualans_table (XX.XXms)
```

---

### Step 2: Update Data Existing (Jika Ada)

Jika Anda sudah memiliki data retur penjualan sebelumnya, perlu update `user_id` untuk data tersebut.

**Opsi A: Jika Hanya 1 User (Single Tenant)**

Jalankan di `php artisan tinker`:
```php
// Update semua retur penjualan dengan user_id = 1
DB::table('retur_penjualans')->update(['user_id' => 1]);
```

**Opsi B: Jika Multi User (Multi Tenant)**

Jalankan di `php artisan tinker`:
```php
// Update berdasarkan penjualan yang terkait
DB::statement("
    UPDATE retur_penjualans rp
    JOIN penjualans p ON rp.penjualan_id = p.id
    SET rp.user_id = p.user_id
");
```

---

### Step 3: Verifikasi

Cek apakah kolom `user_id` sudah ada:

```bash
php artisan tinker
```

```php
// Cek struktur tabel
DB::select("DESCRIBE retur_penjualans");

// Cek data
DB::table('retur_penjualans')->select('id', 'user_id', 'nomor_retur')->get();
```

**Expected Output**:
```
Field: user_id
Type: bigint unsigned
Null: NO
Key: MUL
```

---

### Step 4: Test Halaman Penjualan

1. Buka browser
2. Akses `/transaksi/penjualan`
3. Halaman harus terbuka tanpa error

---

## 📋 Checklist

- [ ] Jalankan `php artisan migrate`
- [ ] Migration berhasil (cek output)
- [ ] Update data existing (jika ada)
- [ ] Verifikasi kolom `user_id` ada di tabel
- [ ] Test buka halaman `/transaksi/penjualan`
- [ ] Halaman terbuka tanpa error ✅

---

## 🔍 Penjelasan Teknis

### Migration File

**File**: `database/migrations/2026_05_06_000001_add_user_id_to_retur_penjualans_table.php`

```php
public function up(): void
{
    Schema::table('retur_penjualans', function (Blueprint $table) {
        // Add user_id column for multi-tenant isolation
        $table->foreignId('user_id')
              ->after('id')
              ->constrained('users')
              ->onDelete('cascade');
        
        // Add index for better query performance
        $table->index('user_id');
    });
}
```

**Yang Dilakukan**:
1. Menambahkan kolom `user_id` setelah kolom `id`
2. Foreign key ke tabel `users`
3. Cascade delete (jika user dihapus, retur juga dihapus)
4. Index untuk performa query

---

### Model ReturPenjualan

**File**: `app/Models/ReturPenjualan.php`

Model sudah siap dengan:

1. **Fillable**:
```php
protected $fillable = [
    'user_id',  // ✅ Already added
    'nomor_retur',
    // ...
];
```

2. **Auto-fill user_id**:
```php
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($returPenjualan) {
        if (empty($returPenjualan->user_id) && auth()->check()) {
            $returPenjualan->user_id = auth()->id();
        }
    });
}
```

---

### Controller Query

**File**: `app/Http/Controllers/PenjualanController.php` (line 86)

```php
$salesReturns = \App\Models\ReturPenjualan::where('user_id', auth()->id())
    ->with(['penjualan', 'detailReturPenjualans.produk'])
    ->orderBy('created_at', 'desc')
    ->get();
```

**Tujuan**: Multi-tenant isolation - setiap user hanya bisa lihat retur penjualan miliknya sendiri.

---

## ⚠️ Troubleshooting

### Error: "SQLSTATE[23000]: Integrity constraint violation"

**Penyebab**: Ada data retur penjualan yang `penjualan_id` tidak valid

**Solusi**:
```php
// Cek data yang bermasalah
DB::select("
    SELECT rp.id, rp.penjualan_id 
    FROM retur_penjualans rp
    LEFT JOIN penjualans p ON rp.penjualan_id = p.id
    WHERE p.id IS NULL
");

// Hapus data yang bermasalah (jika ada)
DB::statement("
    DELETE rp FROM retur_penjualans rp
    LEFT JOIN penjualans p ON rp.penjualan_id = p.id
    WHERE p.id IS NULL
");
```

---

### Error: "SQLSTATE[42000]: Syntax error"

**Penyebab**: Migration sudah pernah dijalankan

**Solusi**:
```bash
# Cek status migration
php artisan migrate:status

# Jika sudah ada, skip migration
```

---

### Error: "Column 'user_id' cannot be null"

**Penyebab**: Ada data existing yang belum di-update

**Solusi**: Jalankan Step 2 (Update Data Existing)

---

## 🎯 Hasil Akhir

Setelah fix ini:

✅ Tabel `retur_penjualans` memiliki kolom `user_id`  
✅ Multi-tenant isolation berfungsi  
✅ Halaman `/transaksi/penjualan` bisa dibuka  
✅ Setiap user hanya bisa lihat retur penjualan miliknya  
✅ Auto-fill `user_id` saat create retur baru  

---

## 📞 Support

Jika masih error setelah migration, beritahu saya:

1. **Output dari `php artisan migrate`**
2. **Error message lengkap** (jika ada)
3. **Hasil dari `DESCRIBE retur_penjualans`**

---

**Status**: 🔧 READY TO FIX  
**Action Required**: Jalankan `php artisan migrate`
