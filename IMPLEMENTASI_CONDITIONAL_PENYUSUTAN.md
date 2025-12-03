# Implementasi Conditional Penyusutan Aset

## 📋 Overview

Sistem penyusutan aset kini menggunakan conditional logic berdasarkan kategori aset. Field penyusutan hanya muncul untuk aset yang memang mengalami penyusutan sesuai standar akuntansi Indonesia.

## ✅ Aset yang DISUSUTKAN

Hanya **Aset Tetap Berwujud** yang memiliki umur manfaat terbatas:

### Yang Disusutkan:
- ✓ Bangunan
- ✓ Kendaraan
- ✓ Mesin
- ✓ Peralatan Kantor
- ✓ Peralatan Produksi
- ✓ Furnitur & Inventaris
- ✓ Komputer & Perangkat IT
- ✓ Aset Tetap Dalam Penyelesaian

**Total: 8 kategori**

## ❌ Aset yang TIDAK DISUSUTKAN

### 1. Aset Lancar (11 kategori)
Tidak disusutkan karena bersifat likuid dan akan dikonversi menjadi kas dalam waktu dekat:
- Kas
- Bank
- Setara Kas
- Piutang Usaha
- Piutang Lainnya
- Persediaan Barang Dagang
- Persediaan Bahan Baku
- Persediaan Barang Jadi
- Perlengkapan
- Uang Muka (Prepaid)
- Investasi Jangka Pendek

### 2. Aset Tetap - Tanah (1 kategori)
- Tanah → Tidak disusutkan karena memiliki umur manfaat tidak terbatas

### 3. Aset Tak Berwujud (6 kategori)
Tidak disusutkan, tetapi **diamortisasi** dengan metode berbeda:
- Hak Cipta
- Paten
- Merek Dagang (Trademark)
- Lisensi Software
- Goodwill
- Franchise

### 4. Investasi Jangka Panjang (5 kategori)
Tidak disusutkan karena nilainya mengikuti nilai pasar:
- Saham Jangka Panjang
- Obligasi Jangka Panjang
- Deposito Jangka Panjang
- Investasi Properti
- Penyertaan Pada Perusahaan Lain

### 5. Aset Lain-Lain (3 kategori)
- Jaminan / Deposit
- Aset Pajak Tangguhan
- Piutang Jangka Panjang

**Total yang tidak disusutkan: 26 kategori**

## 🔧 Implementasi Teknis

### 1. Database Migration
```php
// File: database/migrations/2025_12_03_000002_add_disusutkan_to_kategori_asets.php

// Tambah kolom disusutkan
Schema::table('kategori_asets', function (Blueprint $table) {
    $table->boolean('disusutkan')->default(true)->after('tarif_penyusutan');
});

// Update kategori yang TIDAK disusutkan
DB::table('kategori_asets')
    ->whereIn('nama', $kategoriTidakDisusutkan)
    ->update(['disusutkan' => false]);
```

### 2. Model Update
```php
// File: app/Models/KategoriAset.php

protected $fillable = [
    'jenis_aset_id',
    'kode',
    'nama',
    'deskripsi',
    'umur_ekonomis',
    'tarif_penyusutan',
    'disusutkan', // ← Tambahan
];

protected $casts = [
    'umur_ekonomis' => 'integer',
    'tarif_penyusutan' => 'decimal:2',
    'disusutkan' => 'boolean', // ← Tambahan
];
```

### 3. Controller Validation
```php
// File: app/Http/Controllers/AsetController.php

public function store(Request $request)
{
    // Cek apakah kategori aset disusutkan
    $kategori = KategoriAset::find($request->kategori_aset_id);
    $disusutkan = $kategori ? $kategori->disusutkan : true;
    
    // Validation rules dasar
    $rules = [
        'nama_aset' => 'required|string|max:255',
        'kategori_aset_id' => 'required|exists:kategori_asets,id',
        'harga_perolehan' => 'required|numeric|min:0',
        'biaya_perolehan' => 'required|numeric|min:0',
        'tanggal_beli' => 'required|date',
        'tanggal_akuisisi' => 'nullable|date|after_or_equal:tanggal_beli',
        'keterangan' => 'nullable|string',
    ];
    
    // Tambah validation untuk penyusutan HANYA jika aset disusutkan
    if ($disusutkan) {
        $rules['nilai_residu'] = 'required|numeric|min:0';
        $rules['umur_manfaat'] = 'required|integer|min:1|max:100';
        $rules['metode_penyusutan'] = 'required|in:garis_lurus,saldo_menurun,sum_of_years_digits';
    }
    
    $validated = $request->validate($rules);
    
    // ... rest of the code
}
```

### 4. View - Conditional Display
```javascript
// File: resources/views/master-data/aset/create.blade.php

function checkPenyusutan() {
    const kategoriSelect = document.getElementById('kategori_aset_id');
    const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
    
    const sectionPenyusutan = document.getElementById('section_penyusutan');
    const alertTidakDisusutkan = document.getElementById('alert_tidak_disusutkan');
    
    if (selectedOption && selectedOption.value) {
        const disusutkan = selectedOption.dataset.disusutkan === '1';
        
        if (disusutkan) {
            // Tampilkan form penyusutan
            sectionPenyusutan.style.display = 'block';
            alertTidakDisusutkan.style.display = 'none';
            
            // Set required
            metodePenyusutan.required = true;
            umurManfaat.required = true;
            nilaiResidu.required = true;
        } else {
            // Sembunyikan form penyusutan
            sectionPenyusutan.style.display = 'none';
            alertTidakDisusutkan.style.display = 'block';
            
            // Remove required
            metodePenyusutan.required = false;
            umurManfaat.required = false;
            nilaiResidu.required = false;
            
            // Set nilai default
            metodePenyusutan.value = '';
            umurManfaat.value = 0;
            nilaiResidu.value = 0;
        }
    }
}
```

## 📊 Breakdown Per Jenis Aset

| Jenis Aset | Total Kategori | Disusutkan | Tidak Disusutkan |
|------------|----------------|------------|------------------|
| Aset Lancar | 11 | 0 | 11 |
| Aset Tetap | 9 | 8 | 1 (Tanah) |
| Aset Tak Berwujud | 6 | 0 | 6 |
| Investasi Jangka Panjang | 5 | 0 | 5 |
| Aset Lain-Lain | 3 | 0 | 3 |
| **TOTAL** | **34** | **8** | **26** |

## 🎯 User Experience

### Untuk Aset yang DISUSUTKAN:
1. User memilih kategori aset (misal: Kendaraan)
2. Muncul alert: "Aset ini mengalami penyusutan. Silakan isi informasi penyusutan di bawah."
3. Form penyusutan ditampilkan:
   - Metode Penyusutan (required)
   - Umur Manfaat (required)
   - Nilai Residu (required)
4. Perhitungan otomatis ditampilkan

### Untuk Aset yang TIDAK DISUSUTKAN:
1. User memilih kategori aset (misal: Kas)
2. Muncul alert: "Aset ini tidak mengalami penyusutan. Aset lancar tidak mengalami penyusutan karena bersifat likuid dan akan dikonversi menjadi kas dalam waktu dekat."
3. Form penyusutan DISEMBUNYIKAN
4. Field penyusutan tidak required
5. Nilai penyusutan otomatis di-set ke 0

## ✅ Validasi Standar Akuntansi

Semua validasi PASS:
- ✓ Tanah harus TIDAK disusutkan
- ✓ Kas dan Bank harus TIDAK disusutkan
- ✓ Bangunan harus DISUSUTKAN
- ✓ Kendaraan harus DISUSUTKAN
- ✓ Mesin harus DISUSUTKAN
- ✓ Piutang harus TIDAK disusutkan
- ✓ Persediaan harus TIDAK disusutkan

## 🧪 Testing

Jalankan test untuk verifikasi:
```bash
php test_conditional_penyusutan.php
```

Output akan menampilkan:
1. Daftar kategori yang disusutkan
2. Daftar kategori yang tidak disusutkan
3. Breakdown per jenis aset
4. Validasi sesuai standar akuntansi

## 📝 Files Modified

1. **Migration:**
   - `database/migrations/2025_12_03_000002_add_disusutkan_to_kategori_asets.php`

2. **Model:**
   - `app/Models/KategoriAset.php`

3. **Controller:**
   - `app/Http/Controllers/AsetController.php`

4. **Views:**
   - `resources/views/master-data/aset/create.blade.php`
   - `resources/views/master-data/aset/edit.blade.php`

5. **Test Script:**
   - `test_conditional_penyusutan.php`

## 🎉 Benefits

1. **Sesuai Standar Akuntansi:** Implementasi mengikuti standar akuntansi Indonesia
2. **User-Friendly:** Form otomatis menyesuaikan berdasarkan kategori aset
3. **Validasi Otomatis:** Mencegah input yang salah
4. **Informasi Jelas:** User mendapat penjelasan mengapa aset tidak disusutkan
5. **Efisiensi:** Tidak perlu input data penyusutan untuk aset yang tidak disusutkan

## 📌 Catatan Penting

- **Aset Tak Berwujud** tidak disusutkan tetapi **diamortisasi** (metode berbeda)
- **Tanah** selalu tidak disusutkan karena umur manfaat tidak terbatas
- **Aset Lancar** tidak disusutkan karena bersifat likuid
- **Investasi** tidak disusutkan karena nilainya mengikuti nilai pasar

---

**Status:** ✅ IMPLEMENTED & TESTED
**Date:** 3 Desember 2025
