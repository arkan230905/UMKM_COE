# Fix Dropdown Material - Pendekatan Server-Side

## 🐛 **Masalah**
Dropdown material tidak menampilkan bahan pendukung yang sesuai saat "Bahan Pendukung" dipilih.

## ✅ **Solusi Server-Side**

### Pendekatan yang Digunakan:
1. **Form Submit** - Saat material type berubah, form otomatis submit
2. **Server-Side Rendering** - Controller mengirim data yang sesuai ke view
3. **Conditional Rendering** - View menampilkan dropdown sesuai material_type

### Keunggulan:
- ✅ **Reliable** - Tidak bergantung pada JavaScript/AJAX
- ✅ **Simple** - Menggunakan mekanisme Laravel standar
- ✅ **Compatible** - Bekerja di semua browser
- ✅ **Secure** - Tidak perlu API endpoint tambahan

## 🔧 **Implementasi**

### 1. **View (kartu-stok.blade.php)**
```php
@if(request('material_type') == 'bahan_baku')
    @foreach($bahanBakus as $bb)
        <option value="{{ $bb->id }}">{{ $bb->nama_bahan }}</option>
    @endforeach
@elseif(request('material_type') == 'bahan_pendukung')
    @foreach($bahanPendukungs as $bp)
        <option value="{{ $bp->id }}">{{ $bp->nama_bahan }}</option>
    @endforeach
@endif
```

### 2. **JavaScript**
```javascript
document.querySelector('select[name="material_type"]').addEventListener('change', function() {
    // Clear material selection when type changes
    document.querySelector('select[name="material_id"]').value = '';
    
    // Submit form to reload with new material type
    this.form.submit();
});
```

### 3. **Controller (LaporanKartuStokController.php)**
```php
public function index(Request $request)
{
    $bahanBakus = BahanBaku::with('satuan')->orderBy('nama_bahan')->get();
    $bahanPendukungs = BahanPendukung::with('satuan')->orderBy('nama_bahan')->get();
    
    // Data sudah tersedia untuk view
    return view('laporan.kartu-stok', compact('bahanBakus', 'bahanPendukungs', 'stockMovements'));
}
```

## 🎯 **Cara Kerja**

### Flow:
1. User membuka halaman kartu stok
2. Controller mengirim semua data (bahan baku + bahan pendukung) ke view
3. View menampilkan dropdown material sesuai material_type yang dipilih
4. Saat user mengubah material type:
   - JavaScript clear selection material
   - Form otomatis submit
   - Halaman reload dengan dropdown yang sesuai

### Expected Result:
- ✅ **Bahan Baku** → Dropdown berisi: Ayam Potong, dll
- ✅ **Bahan Pendukung** → Dropdown berisi: Air Galon, Bubuk Bawang Putih, dll
- ✅ **Responsive** → Dropdown langsung berubah saat type dipilih
- ✅ **Maintain State** → Selection tetap terjaga setelah reload

## 🧪 **Testing**

### Test Steps:
1. Buka: `http://127.0.0.1:8000/laporan/kartu-stok`
2. Pilih "Bahan Pendukung" di dropdown pertama
3. Halaman akan reload
4. Dropdown kedua harus berisi:
   - Air Galon
   - Bubuk Bawang Putih
   - (bahan pendukung lainnya)
5. Pilih salah satu material
6. Klik "Tampilkan"

### Expected Result:
- ✅ Dropdown material berubah sesuai jenis yang dipilih
- ✅ Tidak ada error JavaScript
- ✅ Data laporan stok muncul dengan benar
- ✅ Stok awal, pembelian, dan retur tercatat

## 🎉 **Kesimpulan**

Pendekatan server-side ini lebih reliable dan mudah di-maintain dibandingkan AJAX. User experience tetap baik karena reload sangat cepat dan dropdown langsung menampilkan data yang sesuai.