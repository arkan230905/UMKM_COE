# PERBAIKAN PERHITUNGAN BOM - PROCESS COSTING METHOD

## Masalah yang Ditemukan

1. **Perhitungan Tidak Konsisten**
   - Di `create.blade.php`: BTKL = 12% dari bahan baku, BOP = 26% dari BTKL
   - Di `index.blade.php` (controller): BTKL = 60% dari bahan baku, BOP = 40% dari bahan baku
   - Di `store` (controller): BTKL = 60% dari bahan baku, BOP = 40% dari bahan baku

2. **Format Edit Berbeda dengan Create**
   - Edit menggunakan tabel dengan banyak kolom harga yang membingungkan
   - Tidak ada perhitungan real-time seperti di create
   - Format tidak user-friendly

3. **Update Harga Produk Salah**
   - Method `updateProductPrice()` menggunakan `total_biaya` yang belum termasuk BTKL dan BOP
   - Seharusnya menggunakan total biaya produksi (HPP) = Bahan Baku + BTKL + BOP
   - HPP tidak masuk ke kolom `harga_bom` di tabel produk

## Perbaikan yang Dilakukan

### 1. Implementasi Process Costing Method

**Formula Process Costing yang Diterapkan:**
```
Total Bahan Baku = Σ (Harga Satuan × Jumlah dalam KG)
BTKL (Biaya Tenaga Kerja Langsung) = 60% dari Total Bahan Baku
BOP (Biaya Overhead Pabrik) = 40% dari Total Bahan Baku
HPP (Harga Pokok Produksi) = Total Bahan Baku + BTKL + BOP
Harga Jual = HPP × (1 + Margin%)
```

### 2. File yang Diperbaiki

#### A. `resources/views/master-data/bom/create.blade.php`
**Perubahan:**
- ✅ Ubah perhitungan BTKL dari 12% menjadi 60%
- ✅ Ubah perhitungan BOP dari 26% of BTKL menjadi 40% dari bahan baku
- ✅ Ubah label "Grand Total" menjadi "Harga Pokok Produksi"
- ✅ Perhitungan real-time menggunakan JavaScript

**JavaScript yang Diperbaiki:**
```javascript
const btkl = totalBahan * 0.6;  // 60% dari total bahan baku
const bop = totalBahan * 0.4;   // 40% dari total bahan baku
const grandTotal = totalBahan + btkl + bop;
```

#### B. `resources/views/master-data/bom/edit.blade.php`
**Perubahan:**
- ✅ Redesign total mengikuti format create.blade.php
- ✅ Hapus kolom harga yang membingungkan (Harga Utama, Harga 1, 2, 3)
- ✅ Tambahkan perhitungan real-time seperti di create
- ✅ Tambahkan footer dengan total bahan baku, BTKL, BOP, dan HPP
- ✅ Implementasi JavaScript identik dengan create untuk konsistensi
- ✅ Format tabel sama persis dengan create

**Hasil:**
- Edit dan Create sekarang memiliki tampilan dan perhitungan yang identik
- User experience konsisten di semua halaman

#### C. `app/Models/Bom.php`
**Method `hitungTotalBiaya()`:**
```php
public function hitungTotalBiaya()
{
    $totalBahanBaku = $this->details->sum('total_harga');
    
    // Hitung BTKL dan BOP jika belum ada
    if (!$this->total_btkl) {
        $this->total_btkl = $totalBahanBaku * 0.6; // 60%
    }
    if (!$this->total_bop) {
        $this->total_bop = $totalBahanBaku * 0.4; // 40%
    }
    
    // Total biaya = bahan baku + BTKL + BOP
    $this->total_biaya = $totalBahanBaku + $this->total_btkl + $this->total_bop;
    return $this->total_biaya;
}
```

**Method `updateProductPrice()` - PERBAIKAN UTAMA:**
```php
public function updateProductPrice()
{
    if ($this->produk) {
        // Hitung total biaya produksi (HPP) = Bahan Baku + BTKL + BOP
        $totalBahanBaku = $this->details->sum('total_harga');
        $hpp = $totalBahanBaku + $this->total_btkl + $this->total_bop;
        
        // Update harga_bom dengan HPP
        // Update harga_jual dengan HPP + margin
        $margin = $this->produk->margin_percent ?? 0;
        $hargaJual = $hpp * (1 + ($margin / 100));
        
        $this->produk->update([
            'harga_bom' => $hpp,  // ✅ HPP masuk ke kolom harga_bom
            'harga_jual' => $hargaJual  // Harga jual = HPP + margin
        ]);
    }
    return $this;
}
```

#### D. `app/Models/Produk.php`
**Perubahan:**
- ✅ Tambahkan `harga_bom` ke dalam `$fillable`
- Kolom `harga_bom` sudah ada di database (tidak perlu migration baru)

#### E. `app/Http/Controllers/BomController.php`

**Method `store()` - Sudah Benar:**
```php
// Hitung BTKL (60% dari total biaya bahan baku)
$btkl = $totalBiayaBahan * 0.6;

// Hitung BOP (40% dari total biaya bahan baku)
$bopRate = 0.4;
$bop = $totalBiayaBahan * $bopRate;

// Hitung total biaya produksi
$totalBiayaProduksi = $totalBiayaBahan + $btkl + $bop;

// Simpan ke BOM
$bom->total_biaya = $totalBiayaProduksi;
$bom->total_btkl = $btkl;
$bom->total_bop = $bop;

// Update harga produk (akan update harga_bom dan harga_jual)
$bom->updateProductPrice();
```

**Method `update()` - Diperbaiki:**
- ✅ Hapus logika stok yang tidak perlu (tidak ada pengurangan stok di BOM)
- ✅ Standardisasi perhitungan BTKL dan BOP sama dengan store
- ✅ Pastikan `updateProductPrice()` dipanggil untuk update `harga_bom`

**Method `index()` - Sudah Benar:**
- Perhitungan konsisten dengan create dan edit

## Hasil Perbaikan

### Sebelum:
- ❌ Perhitungan berbeda antara create, edit, dan index
- ❌ Harga produk tidak akurat
- ❌ Edit form membingungkan dengan banyak kolom harga
- ❌ Tidak ada perhitungan real-time di edit
- ❌ HPP tidak masuk ke kolom `harga_bom`

### Sesudah:
- ✅ Perhitungan konsisten di semua halaman (Process Costing)
- ✅ Harga produk akurat berdasarkan HPP
- ✅ Edit form identik dengan create (user-friendly)
- ✅ Perhitungan real-time di create dan edit
- ✅ Formula standar: BTKL 60%, BOP 40%
- ✅ **HPP otomatis masuk ke kolom `harga_bom` di tabel produk**
- ✅ Harga jual otomatis dihitung dari HPP + margin

## Alur Kerja Sistem (Process Costing)

### 1. Input Bahan Baku
```
User Input:
- Pilih bahan baku
- Masukkan jumlah
- Pilih satuan

Sistem:
- Konversi ke satuan dasar (KG)
- Ambil harga satuan dari master bahan baku
- Hitung subtotal = harga satuan × jumlah (dalam KG)
```

### 2. Perhitungan Biaya (Process Costing)
```
Step 1: Total Bahan Baku
= Σ subtotal semua bahan

Step 2: BTKL (Biaya Tenaga Kerja Langsung)
= Total Bahan Baku × 60%

Step 3: BOP (Biaya Overhead Pabrik)
= Total Bahan Baku × 40%

Step 4: HPP (Harga Pokok Produksi)
= Total Bahan Baku + BTKL + BOP
```

### 3. Update Harga Produk
```
Step 1: Simpan HPP ke kolom harga_bom
produk.harga_bom = HPP

Step 2: Hitung Harga Jual
harga_jual = HPP × (1 + margin%)

Step 3: Update database
UPDATE produks SET 
  harga_bom = HPP,
  harga_jual = HPP × (1 + margin%)
WHERE id = produk_id
```

## Contoh Perhitungan

### Contoh 1: BOM Roti Tawar
```
Bahan Baku:
- Tepung Terigu: 1 KG × Rp 15.000 = Rp 15.000
- Gula: 0.2 KG × Rp 12.000 = Rp 2.400
- Ragi: 0.05 KG × Rp 50.000 = Rp 2.500
- Mentega: 0.1 KG × Rp 40.000 = Rp 4.000
----------------------------------------
Total Bahan Baku = Rp 23.900

BTKL (60%) = Rp 23.900 × 60% = Rp 14.340
BOP (40%) = Rp 23.900 × 40% = Rp 9.560
----------------------------------------
HPP = Rp 23.900 + Rp 14.340 + Rp 9.560 = Rp 47.800

Margin 30%:
Harga Jual = Rp 47.800 × 1.3 = Rp 62.140

Database Update:
- produks.harga_bom = Rp 47.800 ✅
- produks.harga_jual = Rp 62.140 ✅
```

## Testing Checklist

### ✅ Test Create BOM
1. Buat BOM baru dengan beberapa bahan baku
2. Pastikan perhitungan BTKL = 60% dari bahan baku
3. Pastikan perhitungan BOP = 40% dari bahan baku
4. Pastikan HPP = Bahan Baku + BTKL + BOP
5. **Cek kolom `harga_bom` di tabel produk = HPP**
6. Cek kolom `harga_jual` di tabel produk = HPP × (1 + margin%)

### ✅ Test Edit BOM
1. Edit BOM yang sudah ada
2. Pastikan tampilan identik dengan create
3. Pastikan perhitungan real-time berjalan
4. Ubah jumlah bahan baku, lihat perhitungan update otomatis
5. **Cek kolom `harga_bom` terupdate sesuai HPP baru**
6. Cek kolom `harga_jual` terupdate sesuai perhitungan baru

### ✅ Test Index BOM
1. Lihat daftar BOM
2. Pastikan total biaya produksi ditampilkan dengan benar
3. Bandingkan dengan perhitungan manual
4. Pastikan konsisten dengan create dan edit

## Catatan Penting

### ⚠️ Validasi
- Harga satuan bahan baku harus sudah ada (dari pembelian)
- Jika harga satuan = 0, sistem akan menolak dan memberi peringatan
- Minimal harus ada 1 bahan baku dalam BOM

### 🔧 Teknis
- Semua perhitungan menggunakan satuan dasar KG untuk konsistensi
- Konversi satuan otomatis (G, HG, DAG, ONS → KG)
- Margin produk diambil dari field `margin_percent` di tabel `produks`

### 📊 Database
- `boms.total_biaya` = HPP (Total Biaya Produksi)
- `boms.total_btkl` = BTKL (60% dari bahan baku)
- `boms.total_bop` = BOP (40% dari bahan baku)
- **`produks.harga_bom` = HPP (dari BOM)** ✅
- `produks.harga_jual` = HPP × (1 + margin%)

### 🚫 Yang TIDAK Diubah (Aman untuk Tim)
- ✅ Tidak mengubah struktur database lain
- ✅ Tidak mengubah controller lain
- ✅ Tidak mengubah model lain
- ✅ Tidak mengubah view lain
- ✅ Hanya fokus pada modul BOM
- ✅ Tidak ada data yang hilang atau rusak

## Kesimpulan

Sistem BOM sekarang menggunakan **Process Costing Method** yang standar untuk perusahaan manufaktur:
- ✅ Perhitungan konsisten di semua halaman
- ✅ HPP otomatis masuk ke kolom `harga_bom`
- ✅ Harga jual otomatis dihitung dari HPP + margin
- ✅ Tampilan create dan edit identik
- ✅ Real-time calculation
- ✅ User-friendly dan mudah dipahami
