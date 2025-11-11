# Fix Error BOM - Attempt to read property "kode" on string

## Error
```
ErrorException - Internal Server Error
Attempt to read property "kode" on string
Line 83: resources\views\master-data\bom\create.blade.php
```

## Penyebab
Relasi `$bahan->satuan` bisa berupa:
1. **Object** (relasi Eloquent yang sudah di-load) → `$bahan->satuan->kode` ✅
2. **String** (jika satuan disimpan langsung sebagai string) → `$bahan->satuan->kode` ❌ ERROR
3. **NULL** (jika tidak ada satuan) → `$bahan->satuan->kode` ❌ ERROR

## Solusi

### 1. Di Blade Template (PHP)
**Sebelum**:
```php
$satuanNama = $bahan->satuan ? $bahan->satuan->kode : 'KG';
```

**Sesudah**:
```php
// Cek apakah satuan adalah object atau string
if (is_object($bahan->satuan) && isset($bahan->satuan->kode)) {
    $satuanNama = $bahan->satuan->kode;
} elseif (is_string($bahan->satuan)) {
    $satuanNama = $bahan->satuan;
} else {
    $satuanNama = 'KG';
}
```

### 2. Di JavaScript
**Sebelum**:
```javascript
const satuanNama = bahan.satuan ? bahan.satuan.kode : 'KG';
```

**Sesudah**:
```javascript
// Cek apakah satuan adalah object atau string
let satuanNama = 'KG';
if (bahan.satuan) {
    if (typeof bahan.satuan === 'object' && bahan.satuan.kode) {
        satuanNama = bahan.satuan.kode;
    } else if (typeof bahan.satuan === 'string') {
        satuanNama = bahan.satuan;
    }
}
```

## Penjelasan

### Mengapa Error Terjadi?
Ketika data bahan baku di-load dari database, relasi `satuan` bisa dalam berbagai bentuk:

1. **Eager Loading dengan `with('satuan')`**:
   ```php
   $bahanBakus = BahanBaku::with('satuan')->get();
   // $bahan->satuan adalah object Satuan
   // $bahan->satuan->kode ✅ berfungsi
   ```

2. **Tanpa Eager Loading**:
   ```php
   $bahanBakus = BahanBaku::all();
   // $bahan->satuan bisa jadi string atau null
   // $bahan->satuan->kode ❌ error
   ```

3. **Data Legacy**:
   Jika ada data lama yang menyimpan satuan sebagai string langsung di kolom `satuan`, bukan foreign key.

### Solusi Terbaik
Pastikan di controller selalu eager load relasi:
```php
$bahanBakus = BahanBaku::with('satuan')->get();
```

Tapi tetap tambahkan validasi di view untuk menghindari error jika ada data yang tidak konsisten.

## Testing
1. Buka halaman create BOM
2. Pastikan tidak ada error
3. Dropdown bahan baku muncul dengan benar
4. Pilih bahan baku → satuan muncul dengan benar

## File yang Diperbaiki
- `resources/views/master-data/bom/create.blade.php`
  - Baris 86-93: Validasi satuan di Blade template
  - Baris 277-287: Validasi satuan di JavaScript
