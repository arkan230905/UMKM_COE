# Fix Multi-Tenant Complete - Produk & HPP

## Masalah yang Diperbaiki

### 1. Error di ProdukController
**Error:** `compact(): Undefined variable $kategoris`
**Penyebab:** Variable tidak didefinisikan di method index()

**Solusi:**
- Tambah definisi variable: `$kategoris`, `$search`, `$kategoriFilter`, `$statusFilter`
- Tambah query filter untuk search dan kategori
- Filter kategoris by user_id

### 2. Missing UserScope di Model Penting

**Model yang diperbaiki:**
1. ✅ **Produk** - Tambah UserScope
2. ✅ **HargaPokokProduksiBiayaBahanBaku** - Tambah UserScope
3. ✅ **HargaPokokProduksiBtkl** - Tambah UserScope
4. ✅ **HargaPokokProduksiBop** - Tambah UserScope

## File yang Diubah

### 1. app/Http/Controllers/ProdukController.php
```php
public function index(Request $request)
{
    // CRITICAL: Filter by user_id untuk multi-tenant isolation
    $search = $request->get('search');
    $kategoriFilter = $request->get('kategori');
    $statusFilter = $request->get('status');
    
    // Get all products with filters
    $query = Produk::where('user_id', auth()->id());
    
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('nama_produk', 'like', "%{$search}%")
              ->orWhere('kode_produk', 'like', "%{$search}%");
        });
    }
    
    if ($kategoriFilter) {
        $query->where('kategori_id', $kategoriFilter);
    }
    
    if ($statusFilter) {
        $query->where('status', $statusFilter);
    }
    
    $produks = $query->get();
    
    // Get kategoris for filter dropdown
    $kategoris = \App\Models\KategoriProduk::where('user_id', auth()->id())
        ->orderBy('nama')
        ->get();
    
    // Calculate HPP
    $hargaBom = [];
    foreach ($produks as $produk) {
        $totalBiayaHPP = $produk->getActualHPP();
        $hargaBom[$produk->id] = $totalBiayaHPP;
    }
    
    return view('master-data.produk.index', compact('produks', 'hargaBom', 'kategoris', 'search', 'kategoriFilter', 'statusFilter'));
}
```

### 2. app/Models/Produk.php
```php
protected static function boot()
{
    parent::boot();
    
    // CRITICAL: Apply global scope untuk multi-tenant isolation
    static::addGlobalScope(new \App\Scopes\UserScope);
    
    static::creating(function ($produk) {
        // Auto-fill user_id
        if (empty($produk->user_id) && auth()->check()) {
            $produk->user_id = auth()->id();
        }
        // ... rest of code
    });
}
```

### 3. app/Models/HargaPokokProduksi*.php (3 files)
Semua model HPP sekarang punya:
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
```

## Jantung Sistem yang Sudah Aman (Multi-Tenant)

### ✅ Master Data
1. **BahanBaku** - UserScope ✅
2. **BahanPendukung** - UserScope ✅
3. **Produk** - UserScope ✅ (BARU)
4. **Satuan** - UserScope ✅
5. **COA** - UserScope ✅
6. **Jabatan** - UserScope ✅
7. **Pegawai** - UserScope ✅

### ✅ Biaya Bahan (BBB)
1. **BiayaBahanBaku** - UserScope ✅
2. **BiayaBahanPendukung** - UserScope ✅
3. **BiayaBahanDetail** - UserScope ✅

### ✅ BTKL (Biaya Tenaga Kerja Langsung)
1. **Btkl** - UserScope ✅
2. **ProsesProduksi** - UserScope ✅
3. **HargaPokokProduksiBtkl** - UserScope ✅ (BARU)

### ✅ BOP (Biaya Overhead Pabrik)
1. **Bop** - UserScope ✅
2. **BopProses** - UserScope ✅
3. **HargaPokokProduksiBop** - UserScope ✅ (BARU)

### ✅ HPP (Harga Pokok Produksi)
1. **HargaPokokProduksiBiayaBahanBaku** - UserScope ✅ (BARU)
2. **HargaPokokProduksiBtkl** - UserScope ✅ (BARU)
3. **HargaPokokProduksiBop** - UserScope ✅ (BARU)

### ✅ Transaksi
1. **Pembelian** - UserScope ✅
2. **PembelianDetail** - UserScope ✅
3. **Penjualan** - UserScope ✅
4. **PenjualanDetail** - UserScope ✅
5. **Produksi** - UserScope ✅
6. **ProduksiDetail** - UserScope ✅

### ✅ Stock & Jurnal
1. **StockMovement** - UserScope ✅
2. **JournalEntry** - UserScope ✅
3. **JournalLine** - UserScope ✅

## Cara Kerja UserScope

```php
// UserScope otomatis menambahkan WHERE user_id = auth()->id()
// ke SEMUA query model yang punya scope ini

// Contoh:
Produk::all(); 
// SQL: SELECT * FROM produks WHERE user_id = 2

Produk::where('status', 'active')->get();
// SQL: SELECT * FROM produks WHERE user_id = 2 AND status = 'active'

// Bahkan di relationship:
$produk->biayaBahan;
// SQL: SELECT * FROM biaya_bahan_bakus WHERE produk_id = 1 AND user_id = 2
```

## Testing Multi-Tenant

### Test Case 1: User A tidak bisa lihat data User B
```
User A (id=1):
- Produk: Roti Tawar (id=1)
- HPP: Rp 5.000

User B (id=2):
- Produk: Roti Tawar (id=2)
- HPP: Rp 6.000

Login sebagai User A:
- Produk::all() → Hanya tampil Roti Tawar (id=1) ✅
- Tidak bisa akses Produk id=2 ✅

Login sebagai User B:
- Produk::all() → Hanya tampil Roti Tawar (id=2) ✅
- Tidak bisa akses Produk id=1 ✅
```

### Test Case 2: HPP Calculation Isolated
```
User A:
- Bahan Baku: Tepung Rp 10.000/kg
- BTKL: Rp 5.000/unit
- BOP: Rp 3.000/unit
- HPP = Rp 18.000/unit

User B:
- Bahan Baku: Tepung Rp 12.000/kg
- BTKL: Rp 6.000/unit
- BOP: Rp 4.000/unit
- HPP = Rp 22.000/unit

Tidak boleh tercampur! ✅
```

## Catatan Penting

1. **UserScope** otomatis apply di semua query
2. **user_id** otomatis terisi saat create
3. **Semua relationship** juga ter-filter by user_id
4. **Jangan bypass** UserScope kecuali untuk admin global

## Tanggal
2026-05-06 21:20
