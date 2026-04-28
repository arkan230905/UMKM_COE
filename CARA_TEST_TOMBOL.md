# Cara Test Tombol Tambah & Hapus Anggota Tim

## Langkah-langkah Testing

### 1. Buka Halaman Kelola Catalog
```
http://localhost/kelola-catalog
atau
http://your-domain/kelola-catalog
```

### 2. Buka Browser Console (PENTING!)
**Windows:**
- Chrome/Edge: Tekan `F12` atau `Ctrl + Shift + I`
- Firefox: Tekan `F12` atau `Ctrl + Shift + K`

**Mac:**
- Chrome/Edge: Tekan `Cmd + Option + I`
- Firefox: Tekan `Cmd + Option + K`

### 3. Periksa Console Output

Setelah halaman dimuat, Anda harus melihat output seperti ini di console:

```
=== TEAM MEMBER FUNCTIONS VERIFICATION ===
Team member functions defined: {
  addTeamMemberRow: "function",
  removeTeamMemberRow: "function", 
  updateTeamRemoveButtons: "function"
}
addTeamMemberRow function: ƒ addTeamMemberRow()
removeTeamMemberRow function: ƒ removeTeamMemberRow(button)
===========================================

=== KELOLA CATALOG PAGE READY ===
jQuery version: 3.6.0
Add team member button exists: true
Team members list exists: true
Initial team member count: 2
==================================
Remove buttons updated. Total members: 2
```

✅ **Jika melihat output di atas, berarti fungsi sudah terdefinisi dengan benar!**

### 4. Test Tombol "Tambah Anggota"

1. Scroll ke section "Team Section"
2. Cari tombol hijau dengan text "Tambah Anggota"
3. **Klik tombol tersebut**

**Yang Harus Terjadi:**
- ✅ Baris baru muncul di bawah anggota yang sudah ada
- ✅ Baris baru berisi kolom: Foto, Nama Lengkap, Jabatan, Deskripsi Singkat, Tombol Hapus
- ✅ Console menampilkan:
  ```
  addTeamMemberRow called
  Team member added. New count: 3
  Remove buttons updated. Total members: 3
  Button clicked via jQuery event listener!
  ```

### 5. Test Tombol "Hapus" (Trash Icon)

1. Pada salah satu baris anggota tim, cari tombol merah dengan icon trash
2. **Klik tombol trash tersebut**

**Yang Harus Terjadi:**
- ✅ Baris anggota tersebut hilang/terhapus
- ✅ Console menampilkan:
  ```
  removeTeamMemberRow called
  Team member removed. New count: 2
  Remove buttons updated. Total members: 2
  ```

### 6. Test Minimal 1 Anggota

1. Tambah beberapa anggota (klik "Tambah Anggota" beberapa kali)
2. Hapus anggota satu per satu sampai hanya tersisa 1 anggota
3. **Perhatikan tombol hapus pada anggota terakhir**

**Yang Harus Terjadi:**
- ✅ Tombol hapus pada anggota terakhir **HILANG** (display: none)
- ✅ Console menampilkan:
  ```
  Remove buttons updated. Total members: 1
  ```

4. Jika Anda mencoba menambah anggota lagi, tombol hapus akan muncul kembali

### 7. Test Upload Foto Anggota

1. Pada salah satu baris anggota, klik input "Foto"
2. Pilih file gambar (JPG, PNG, GIF, max 5MB)
3. **Klik Open/Pilih**

**Yang Harus Terjadi:**
- ✅ Preview foto muncul di bawah input file
- ✅ Ada tombol X merah di pojok kanan atas preview untuk menghapus foto

### 8. Test Simpan Data

1. Isi semua data anggota tim (nama, jabatan, deskripsi)
2. Upload foto jika diperlukan
3. Scroll ke bawah dan klik tombol "Update Semua Data"

**Yang Harus Terjadi:**
- ✅ Tombol berubah menjadi "Menyimpan..." dengan spinner
- ✅ Setelah selesai, muncul SweetAlert success: "Berhasil!"
- ✅ Data tersimpan di database

## Troubleshooting

### ❌ Tombol Tidak Merespon Klik

**Solusi:**
1. Buka console (F12)
2. Ketik: `typeof addTeamMemberRow`
3. Jika return `"undefined"`, berarti fungsi tidak terdefinisi
4. Refresh halaman dengan Ctrl+F5 (hard refresh)
5. Clear browser cache

### ❌ Console Menampilkan Error

**Contoh Error:**
```
Uncaught ReferenceError: addTeamMemberRow is not defined
```

**Solusi:**
1. Pastikan file `resources/views/kelola-catalog/index.blade.php` sudah disimpan
2. Clear Laravel cache:
   ```bash
   php artisan view:clear
   php artisan cache:clear
   ```
3. Refresh browser dengan Ctrl+F5

### ❌ Fungsi Terdefinisi Tapi Tombol Tidak Berfungsi

**Solusi:**
1. Inspect element tombol "Tambah Anggota"
2. Periksa apakah ada atribut `onclick="addTeamMemberRow()"`
3. Jika tidak ada, berarti HTML tidak ter-render dengan benar
4. Clear view cache: `php artisan view:clear`

### ❌ Tombol Hapus Tidak Muncul

**Solusi:**
1. Pastikan ada lebih dari 1 anggota tim
2. Buka console dan ketik: `updateTeamRemoveButtons()`
3. Tombol hapus harus muncul jika ada > 1 anggota

## Verifikasi Manual di Console

Anda bisa test fungsi secara manual di browser console:

### Test addTeamMemberRow:
```javascript
addTeamMemberRow()
```
Harus menambah baris baru.

### Test removeTeamMemberRow:
```javascript
// Ambil tombol hapus pertama
const btn = document.querySelector('.remove-member');
removeTeamMemberRow(btn);
```
Harus menghapus baris.

### Test updateTeamRemoveButtons:
```javascript
updateTeamRemoveButtons()
```
Harus update visibility tombol hapus.

### Cek Jumlah Anggota:
```javascript
document.querySelectorAll('.team-member-item').length
```
Harus return jumlah anggota saat ini.

## Kesimpulan

Jika semua test di atas berhasil, berarti:
✅ Tombol Tambah Anggota berfungsi
✅ Tombol Hapus berfungsi
✅ Auto-hide tombol hapus berfungsi
✅ Upload foto berfungsi
✅ Simpan data berfungsi

**Status: SIAP DIGUNAKAN! 🎉**
