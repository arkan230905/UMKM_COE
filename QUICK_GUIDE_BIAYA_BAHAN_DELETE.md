# Quick Guide: Hapus Data Biaya Bahan

## 🎯 2 Cara Menghapus Data

### 1️⃣ Hapus Baris Individual (di Halaman Edit)
**Lokasi**: `master-data/biaya-bahan/edit/[id]`

**Langkah**:
1. Buka halaman edit biaya bahan untuk produk tertentu
2. Lihat tabel Bahan Baku atau Bahan Pendukung
3. Klik tombol 🗑️ (trash icon) di kolom **Aksi** pada baris yang ingin dihapus
4. Baris akan langsung terhapus
5. Total otomatis dihitung ulang
6. Klik **Simpan Perubahan** untuk menyimpan

**Catatan**: 
- Ini hanya menghapus 1 baris bahan
- Perubahan baru tersimpan setelah klik "Simpan Perubahan"
- Bisa undo dengan refresh halaman (sebelum simpan)

---

### 2️⃣ Hapus Semua Data Biaya Bahan (di Halaman Index)
**Lokasi**: `master-data/biaya-bahan`

**Langkah**:
1. Buka halaman utama biaya bahan
2. Cari produk yang ingin dihapus semua data biaya bahannya
3. Klik tombol 🗑️ (trash icon) di kolom **Aksi**
4. Muncul konfirmasi:
   ```
   PERHATIAN!
   
   Anda akan menghapus SEMUA data biaya bahan untuk produk:
   [Nama Produk]
   
   Ini akan menghapus:
   - Semua Bahan Baku
   - Semua Bahan Pendukung
   - Reset harga BOM menjadi Rp 0
   
   Yakin ingin melanjutkan?
   ```
5. Klik **OK** untuk konfirmasi
6. Semua data biaya bahan untuk produk tersebut akan terhapus
7. Muncul notifikasi: "Semua data biaya bahan untuk produk '[Nama Produk]' berhasil dihapus."

**Catatan**:
- Ini menghapus SEMUA data biaya bahan untuk produk
- Tidak bisa undo setelah konfirmasi
- Harga BOM akan reset ke Rp 0
- Data produk tetap ada, hanya biaya bahannya yang dihapus

---

## 🔍 Perbedaan Kedua Cara

| Aspek | Hapus Baris (Edit) | Hapus Semua (Index) |
|-------|-------------------|---------------------|
| **Lokasi** | Halaman Edit | Halaman Index |
| **Yang Dihapus** | 1 baris bahan | Semua data biaya bahan |
| **Konfirmasi** | Tidak ada | Ada konfirmasi |
| **Simpan** | Perlu klik "Simpan" | Langsung tersimpan |
| **Undo** | Bisa (refresh sebelum simpan) | Tidak bisa |
| **Harga BOM** | Update setelah simpan | Reset ke Rp 0 |

---

## ⚠️ PERINGATAN

### Hapus Baris Individual:
- ✅ Aman untuk menghapus bahan yang salah input
- ✅ Bisa undo dengan refresh (sebelum simpan)
- ⚠️ Jangan lupa klik "Simpan Perubahan"

### Hapus Semua Data:
- ⚠️ **TIDAK BISA UNDO** setelah konfirmasi
- ⚠️ Semua bahan baku dan pendukung akan terhapus
- ⚠️ Harga BOM akan reset ke Rp 0
- ✅ Gunakan jika ingin reset ulang perhitungan biaya bahan

---

## 🧪 Testing

### Test Hapus Baris:
1. Buka `master-data/biaya-bahan/edit/4` (atau ID lain)
2. Klik tombol 🗑️ pada baris pertama
3. ✅ Baris harus langsung hilang
4. ✅ Total harus otomatis update
5. Refresh halaman (Ctrl+F5)
6. ✅ Baris kembali muncul (karena belum disimpan)

### Test Hapus Semua:
1. Buka `master-data/biaya-bahan`
2. Pilih produk yang memiliki data biaya bahan
3. Klik tombol 🗑️
4. ✅ Muncul konfirmasi yang jelas
5. Klik OK
6. ✅ Muncul notifikasi sukses
7. ✅ Harga BOM produk menjadi Rp 0
8. Buka halaman edit produk tersebut
9. ✅ Tidak ada data bahan baku dan pendukung

---

## 🐛 Troubleshooting

### Tombol Hapus Tidak Berfungsi (Edit):
1. Buka Browser Console (F12)
2. Cek apakah ada error JavaScript
3. Coba hard refresh: `Ctrl + F5`
4. Cek apakah script loaded: lihat console log "Biaya Bahan Edit - Script loaded"

### Tombol Hapus Tidak Berfungsi (Index):
1. Cek apakah form submit
2. Cek Laravel log: `storage/logs/laravel.log`
3. Cari log: "Deleting biaya bahan for product"
4. Cek apakah ada error

### Data Tidak Terhapus:
1. Cek Laravel log untuk error
2. Cek database:
   ```sql
   SELECT * FROM bom_details WHERE bom_id IN (SELECT id FROM boms WHERE produk_id = [ID]);
   SELECT * FROM bom_job_bahan_pendukungs WHERE bom_job_costing_id IN (SELECT id FROM bom_job_costings WHERE produk_id = [ID]);
   ```
3. Jika masih ada data, cek method `destroy()` di controller

---

## 📝 Log yang Dicatat

Saat hapus semua data, sistem mencatat:
```
[timestamp] Deleting biaya bahan for product {"produk_id":4}
[timestamp] Deleted BomDetail {"id":123}
[timestamp] Deleted BomDetail {"id":124}
[timestamp] Deleted BomJobBahanPendukung {"count":2}
[timestamp] Deleted BomJobCosting {"id":45}
[timestamp] Reset harga_bom to 0 {"produk_id":4}
```

Cek log di: `storage/logs/laravel.log`

---

## ✅ Checklist Setelah Delete

- [ ] Notifikasi sukses muncul
- [ ] Data biaya bahan terhapus dari database
- [ ] Harga BOM reset ke Rp 0 (untuk hapus semua)
- [ ] Tidak ada error di Laravel log
- [ ] Tidak ada error di Browser console
- [ ] Halaman index menampilkan data yang benar

---

**Sistem siap digunakan!** 🚀
