# Perbaikan Modul Aset - Fix Kolom Kosong & Gagal Simpan

## Masalah yang Diperbaiki

### 1. Kolom "Jenis Aset" Menampilkan "-"
**Penyebab:** Di view `index.blade.php` menggunakan `$aset->jenisAset->nama` tetapi model Aset tidak memiliki relasi `jenisAset()`. Relasi yang ada adalah `kategori()`, dan jenis aset berada di `kategori->jenisAset`.

**Solusi:** 
- Ubah dari: `{{ $aset->jenisAset->nama ?? '-' }}`
- Menjadi: `{{ $aset->kategori->jenisAset->nama ?? '-' }}`

**File yang diubah:** `resources/views/master-data/aset/index.blade.php` (baris 105)

---

### 2. Kolom "Kategori" & "Nilai Buku" Kosong Saat Simpan
**Penyebab:** 
- Di controller `store()`, setelah membuat/mencari `$kategoriAset`, tidak di-set ke `$aset->kategori_aset_id`
- Kode lama: `$aset->kategori_aset_id = $request->kategori_aset_id;` (field ini tidak ada di form)
- Seharusnya: `$aset->kategori_aset_id = $kategoriAset->id;`

**Solusi:**
- Set `kategori_aset_id` dari ID kategori yang ditemukan/dibuat
- Ubah baris 124 dari: `$aset->kategori_aset_id = $request->kategori_aset_id;`
- Menjadi: `$aset->kategori_aset_id = $kategoriAset->id;`

**File yang diubah:** `app/Http/Controllers/AsetController.php` (baris 124)

---

### 3. Form Tidak Bisa Tersimpan - Validasi Gagal
**Penyebab:** Nama field di form tidak cocok dengan validasi di controller:
- Form mengirim `acquisition_cost`, validasi mengharap `biaya_perolehan`
- Form mengirim `residual_value`, validasi mengharap `nilai_residu`

**Solusi:**
- Update validasi di `store()` agar sesuai dengan nama field form:
  - Ganti `'biaya_perolehan'` → `'acquisition_cost'`
  - Ganti `'nilai_residu'` → `'residual_value'`
- Update pengambilan nilai di controller:
  - Ubah: `$biayaPerolehan = (float) ($request->biaya_perolehan ?? 0);`
  - Menjadi: `$biayaPerolehan = (float) ($request->acquisition_cost ?? 0);`
  - Ubah: `$nilaiResidu = $request->filled('nilai_residu') ? (float) $request->nilai_residu : ...`
  - Menjadi: `$nilaiResidu = $request->filled('residual_value') ? (float) $request->residual_value : ...`

**File yang diubah:** `app/Http/Controllers/AsetController.php` (baris 77-115)

---

### 4. Filter Kategori Tidak Bekerja
**Penyebab:** 
- View filter menggunakan `name="kategori_aset_id"`
- Controller membaca `$request->kategori_aset` (nama berbeda)

**Solusi:**
- Ubah controller untuk membaca parameter yang benar:
  - Dari: `if ($request->has('kategori_aset') && !empty($request->kategori_aset))`
  - Menjadi: `if ($request->has('kategori_aset_id') && !empty($request->kategori_aset_id))`
  - Dari: `$query->where('kategori_aset_id', $request->kategori_aset);`
  - Menjadi: `$query->where('kategori_aset_id', $request->kategori_aset_id);`

**File yang diubah:** `app/Http/Controllers/AsetController.php` (baris 38-41)

---

## File yang Dimodifikasi

1. **app/Http/Controllers/AsetController.php**
   - Baris 38-41: Fix filter kategori_aset_id
   - Baris 76-90: Update validasi form fields
   - Baris 109: Update pengambilan acquisition_cost
   - Baris 113-115: Update pengambilan residual_value
   - Baris 124: Set kategori_aset_id dari $kategoriAset->id

2. **resources/views/master-data/aset/index.blade.php**
   - Baris 105: Fix tampilan Jenis Aset

---

## Testing Checklist

- [ ] Buka halaman "Daftar Aset" - kolom Jenis Aset harus menampilkan nama (bukan "-")
- [ ] Buka halaman "Tambah Aset Baru"
- [ ] Isi form dengan data:
  - Nama Aset: "Test Aset"
  - Jenis Aset: "Aset Tetap"
  - Kategori: "Peralatan Kantor"
  - Harga Perolehan: "1000000"
  - Biaya Perolehan: "100000"
  - Umur Manfaat: "5"
  - Tanggal Beli: (hari ini)
  - Tanggal Akuisisi: (hari ini)
  - Status: "Aktif"
- [ ] Klik "Simpan" - seharusnya berhasil
- [ ] Cek di tabel - kolom Jenis Aset, Kategori, dan Nilai Buku harus terisi
- [ ] Test filter Jenis Aset dan Kategori - harus bekerja

---

## Catatan

- Semua perubahan dilakukan pada file existing (tidak membuat file baru)
- Tidak ada perubahan pada struktur database
- Tidak ada perubahan pada model atau migration
- Perubahan bersifat minimal dan fokus pada fix bug
