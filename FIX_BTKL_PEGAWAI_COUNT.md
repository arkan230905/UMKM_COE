# ✅ Fix: Jumlah Pegawai di BTKL Menampilkan 0

## 🐛 Masalah

Halaman BTKL create dan edit tidak membaca jumlah pegawai dengan benar, sehingga:
- Jumlah pegawai selalu menampilkan **0**
- Tarif BTKL menjadi **Rp 0** (karena Tarif Jabatan × 0 pegawai = 0)
- Biaya per produk juga menjadi **Rp 0**

## 🔍 Penyebab

Di **controller**, data pegawai sudah di-map dengan benar:

```php
$employeeData = $jabatanBtkl->map(function($jabatan) {
    return [
        'id' => $jabatan->id,
        'nama' => $jabatan->nama,
        'pegawai_count' => $jabatan->pegawais->count(), // ✅ Sudah benar
        'tarif' => $jabatan->tarif ?? 0
    ];
});
```

Tapi di **view**, JavaScript menggunakan data yang salah:

```javascript
// ❌ SALAH - menggunakan $jabatanBtkl yang tidak punya pegawai_count
const employeeData = @json($jabatanBtkl ?? []);
```

Seharusnya:

```javascript
// ✅ BENAR - menggunakan $employeeData yang sudah di-map
const employeeData = @json($employeeData ?? []);
```

## 🔧 Perbaikan

### 1. File: `app/Http/Controllers/MasterData/BtklController.php`

**Method `edit()`** - Tambahkan `$employeeData` ke view:

```php
public function edit($id)
{
    try {
        $btkl = Btkl::with('jabatan')->findOrFail($id);
        $jabatanBtkl = Jabatan::where('kategori', 'btkl')
            ->with('pegawais')
            ->orderBy('nama')
            ->get();
        $satuanOptions = ['Jam', 'Unit', 'Batch'];
        
        // ✅ TAMBAHKAN INI - Map employee data dengan pegawai_count
        $employeeData = $jabatanBtkl->map(function($jabatan) {
            return [
                'id' => $jabatan->id,
                'nama' => $jabatan->nama,
                'pegawai_count' => $jabatan->pegawais->count(),
                'tarif' => $jabatan->tarif ?? 0
            ];
        });
            
        // ✅ TAMBAHKAN 'employeeData' ke compact
        return view('master-data.btkl.edit', compact('btkl', 'jabatanBtkl', 'satuanOptions', 'employeeData'));
        
    } catch (\Exception $e) {
        return redirect()
            ->route('master-data.btkl.index')
            ->with('error', 'Data BTKL tidak ditemukan: ' . $e->getMessage());
    }
}
```

### 2. File: `resources/views/master-data/btkl/create.blade.php`

**Bagian JavaScript** - Ganti `$jabatanBtkl` dengan `$employeeData`:

```javascript
<script>
// ✅ FIXED: menggunakan employeeData yang sudah di-map dengan pegawai_count
const employeeData = @json($employeeData ?? []);

document.addEventListener('DOMContentLoaded', function() {
    // ... rest of code
});
</script>
```

### 3. File: `resources/views/master-data/btkl/edit.blade.php`

**Bagian JavaScript** - Sama seperti create:

```javascript
<script>
// ✅ FIXED: menggunakan employeeData yang sudah di-map dengan pegawai_count
const employeeData = @json($employeeData ?? []);

document.addEventListener('DOMContentLoaded', function() {
    // ... rest of code
});
</script>
```

## ✅ Hasil Setelah Perbaikan

### Sebelum:
```
Jabatan: Perbumbuan
Tarif Jabatan: Rp 18.000
Jumlah Pegawai: 0 ❌
Tarif BTKL: Rp 0 ❌
```

### Sesudah:
```
Jabatan: Perbumbuan
Tarif Jabatan: Rp 18.000
Jumlah Pegawai: 2 ✅
Tarif BTKL: Rp 36.000 ✅ (Rp 18.000 × 2 pegawai)
```

## 🧪 Testing

### Test di Halaman Create:

1. Buka halaman **Tambah BTKL**
2. Pilih jabatan (misal: Perbumbuan)
3. **Verifikasi:**
   - Jumlah pegawai muncul dengan benar (bukan 0)
   - Tarif BTKL dihitung otomatis: Tarif Jabatan × Jumlah Pegawai
   - Biaya per produk dihitung: Tarif BTKL ÷ Kapasitas

### Test di Halaman Edit:

1. Buka halaman **Edit BTKL**
2. Ganti jabatan ke jabatan lain
3. **Verifikasi:**
   - Jumlah pegawai update sesuai jabatan yang dipilih
   - Tarif BTKL update otomatis
   - Biaya per produk update otomatis

## 📊 Contoh Perhitungan

### Jabatan: Perbumbuan
- **Tarif Jabatan:** Rp 18.000/jam
- **Jumlah Pegawai:** 2 orang
- **Tarif BTKL:** Rp 18.000 × 2 = **Rp 36.000/jam**
- **Kapasitas:** 100 pcs/jam
- **Biaya per Produk:** Rp 36.000 ÷ 100 = **Rp 360/pcs**

### Jabatan: Penggorengan
- **Tarif Jabatan:** Rp 20.000/jam
- **Jumlah Pegawai:** 3 orang
- **Tarif BTKL:** Rp 20.000 × 3 = **Rp 60.000/jam**
- **Kapasitas:** 150 pcs/jam
- **Biaya per Produk:** Rp 60.000 ÷ 150 = **Rp 400/pcs**

## 🔍 Debug

Jika masih menampilkan 0, cek:

### 1. Cek Data Pegawai di Database:

```sql
-- Cek jumlah pegawai per jabatan
SELECT 
    j.id,
    j.nama as jabatan,
    COUNT(p.id) as jumlah_pegawai
FROM jabatans j
LEFT JOIN pegawais p ON p.jabatan_id = j.id
WHERE j.kategori = 'btkl'
GROUP BY j.id, j.nama;
```

### 2. Cek di Browser Console:

```javascript
// Buka browser console (F12)
console.log(employeeData);

// Output seharusnya:
// [
//   {id: 1, nama: "Perbumbuan", pegawai_count: 2, tarif: 18000},
//   {id: 2, nama: "Penggorengan", pegawai_count: 3, tarif: 20000}
// ]
```

### 3. Cek Relasi Model:

File: `app/Models/Jabatan.php`

```php
public function pegawais()
{
    return $this->hasMany(Pegawai::class, 'jabatan_id');
}
```

File: `app/Models/Pegawai.php`

```php
public function jabatanRelasi()
{
    return $this->belongsTo(Jabatan::class, 'jabatan_id');
}
```

## 📝 Catatan

- Perbaikan ini **tidak mengubah logika bisnis**
- Hanya memperbaiki **passing data** dari controller ke view
- **Tidak ada perubahan database** diperlukan
- **Tidak ada perubahan model** diperlukan

---

**Tanggal:** 17 April 2026

**Status:** ✅ **FIXED**

**File yang Diubah:**
1. `app/Http/Controllers/MasterData/BtklController.php` (method `edit`)
2. `resources/views/master-data/btkl/create.blade.php` (JavaScript)
3. `resources/views/master-data/btkl/edit.blade.php` (JavaScript)
