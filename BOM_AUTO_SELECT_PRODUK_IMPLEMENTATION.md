# BOM Auto Select Produk Implementation

## Problem
User harus memilih produk lagi saat klik "Tambah BOM" atau "Edit BOM" dari halaman index, padahal produk sudah jelas dari tombol yang diklik.

## User Experience Issue
1. User klik "Tambah BOM" pada produk tertentu
2. Halaman create terbuka tapi masih harus pilih produk lagi
3. Tidak efisien dan membingungkan user

## Solution
Implementasi auto-select produk berdasarkan parameter URL dan context.

## Changes Made

### 1. Updated BOM Controller Create Method
```php
// Before
public function create()
{
    $produks = Produk::whereNotIn('id', $produkIdsWithBom)->get();
    // Tidak menangani parameter produk_id
}

// After
public function create(Request $request)
{
    $selectedProdukId = $request->get('produk_id');
    
    if ($selectedProdukId) {
        // Auto-select produk dari parameter URL
        $selectedProduk = Produk::find($selectedProdukId);
        $produks = collect([$selectedProduk]);
    } else {
        // Fallback ke semua produk yang belum punya BOM
        $produks = Produk::whereNotIn('id', $produkIdsWithBom)->get();
    }
}
```

### 2. Updated Create View - Smart Product Selection
```php
@if($selectedProduk)
    {{-- Produk sudah terpilih otomatis --}}
    <input type="hidden" name="produk_id" value="{{ $selectedProduk->id }}">
    <div class="form-control-plaintext bg-light p-3 rounded border">
        {{-- Tampilan produk dengan foto dan info lengkap --}}
    </div>
    <small class="text-success">Produk sudah terpilih otomatis</small>
@else
    {{-- Dropdown untuk pilih produk manual --}}
    <select name="produk_id" id="produk_id" class="form-select" required>
        {{-- Options untuk semua produk --}}
    </select>
@endif
```

### 3. Enhanced Edit View - Consistent Product Display
```php
// Before: Simple readonly input
<input type="text" class="form-control" value="{{ $bom->produk->nama_produk }}" readonly>

// After: Rich product display with photo
<div class="form-control-plaintext bg-light p-3 rounded border">
    <div class="d-flex align-items-center">
        {{-- Product photo and details --}}
    </div>
</div>
<small class="text-info">Produk tidak dapat diubah saat edit BOM</small>
```

### 4. Dynamic Page Title
```php
// Create page title
@if($selectedProduk)
    Buat BOM: {{ $selectedProduk->nama_produk }}
@else
    Buat BOM (Bill of Materials)
@endif

// Edit page title (already implemented)
Edit BOM: {{ $bom->produk->nama_produk }}
```

## User Flow Improvements

### Scenario 1: Tambah BOM dari Index
1. User klik "Tambah BOM" pada produk "Ayam Ketumbar"
2. URL: `/master-data/bom/create?produk_id=1`
3. Halaman create terbuka dengan produk "Ayam Ketumbar" sudah terpilih
4. User langsung bisa isi biaya bahan, BTKL, dan BOP
5. ✅ **No need to select product again**

### Scenario 2: Edit BOM
1. User klik "Edit" pada BOM produk "Ayam Pop"
2. Halaman edit terbuka dengan produk "Ayam Pop" ditampilkan (read-only)
3. User langsung bisa edit komponen BOM
4. ✅ **Product is locked and clearly displayed**

### Scenario 3: Manual Create (Fallback)
1. User akses `/master-data/bom/create` langsung
2. Dropdown produk muncul untuk dipilih manual
3. ✅ **Backward compatibility maintained**

## Validation & Error Handling

### Product Already Has BOM
```php
if ($selectedProduk && $selectedProduk->boms->count() > 0) {
    return redirect()->route('master-data.bom.index')
        ->with('error', 'Produk "' . $selectedProduk->nama_produk . '" sudah memiliki BOM.');
}
```

### Product Not Found
```php
if ($selectedProdukId && !$selectedProduk) {
    return redirect()->route('master-data.bom.index')
        ->with('error', 'Produk tidak ditemukan.');
}
```

## UI/UX Enhancements

### Visual Indicators
- ✅ **Success message**: "Produk sudah terpilih otomatis"
- 🔒 **Lock indicator**: "Produk tidak dapat diubah saat edit BOM"
- 📷 **Product photos**: Consistent display with index page
- 🏷️ **Product info**: Name, barcode, visual consistency

### Responsive Design
- Product display adapts to screen size
- Photo placeholder for products without images
- Consistent styling with index page

## Files Modified
1. `app/Http/Controllers/BomController.php` - Enhanced create method
2. `resources/views/master-data/bom/create.blade.php` - Smart product selection
3. `resources/views/master-data/bom/edit.blade.php` - Enhanced product display

## Testing Scenarios
✅ Click "Tambah BOM" from index → Product auto-selected
✅ Click "Edit BOM" from index → Product displayed as read-only
✅ Direct access to create page → Manual product selection works
✅ Invalid produk_id parameter → Proper error handling
✅ Product already has BOM → Validation prevents duplicate

## Status
**COMPLETED** - Users no longer need to select products again when creating/editing BOM from index page.