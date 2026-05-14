# Debug: Tombol Tambah & Hapus Anggota Tim

## 🔍 Langkah-Langkah Debugging

### 1. Buka Halaman Kelola Catalog
- Buka browser ke `/kelola-catalog`
- Buka Developer Tools (F12)
- Buka tab "Console"

### 2. Cek Apakah Fungsi Sudah Loaded

Ketik di console:

```javascript
// Cek apakah fungsi ada
console.log('addTeamMemberRow:', typeof addTeamMemberRow);
console.log('removeTeamMemberRow:', typeof removeTeamMemberRow);
console.log('updateTeamRemoveButtons:', typeof updateTeamMemberRow);
```

**Expected Output:**
```
addTeamMemberRow: function
removeTeamMemberRow: function
updateTeamRemoveButtons: function
```

**Jika Output: "undefined"**
- Berarti fungsi belum loaded
- Cek apakah ada error JavaScript di console
- Scroll ke bawah untuk melihat error

### 3. Cek Apakah Tombol Ada

```javascript
// Cek tombol tambah
console.log('Tombol tambah:', document.getElementById('addTeamMember'));

// Cek container
console.log('Container:', document.getElementById('teamMembersList'));

// Cek jumlah anggota
console.log('Jumlah anggota:', document.querySelectorAll('.team-member-item').length);
```

**Expected Output:**
```
Tombol tambah: <button id="addTeamMember" ...>
Container: <div id="teamMembersList" ...>
Jumlah anggota: 2
```

### 4. Test Manual Call Fungsi

```javascript
// Test tambah anggota
addTeamMemberRow();

// Cek jumlah setelah tambah
console.log('Jumlah setelah tambah:', document.querySelectorAll('.team-member-item').length);
```

**Expected Output:**
```
addTeamMemberRow called
Team member added. New count: 3
Remove buttons updated. Total members: 3
Jumlah setelah tambah: 3
```

### 5. Test Tombol Hapus

```javascript
// Get tombol hapus pertama
const btn = document.querySelector('.remove-member');
console.log('Tombol hapus:', btn);

// Test click
btn.click();
```

**Expected Output:**
```
removeTeamMemberRow called
Team member removed. New count: 2
Remove buttons updated. Total members: 2
```

### 6. Cek onclick Attribute

```javascript
// Cek onclick pada tombol tambah
const addBtn = document.getElementById('addTeamMember');
console.log('onclick tambah:', addBtn.getAttribute('onclick'));

// Cek onclick pada tombol hapus
const removeBtn = document.querySelector('.remove-member');
console.log('onclick hapus:', removeBtn.getAttribute('onclick'));
```

**Expected Output:**
```
onclick tambah: addTeamMemberRow()
onclick hapus: removeTeamMemberRow(this)
```

## 🐛 Common Issues & Solutions

### Issue 1: Fungsi "undefined"

**Penyebab:**
- JavaScript error sebelum fungsi dideklarasikan
- Fungsi di dalam scope yang salah

**Solusi:**
1. Cek console untuk error
2. Pastikan tidak ada syntax error
3. Pastikan fungsi di global scope (bukan dalam $(document).ready))

**Test:**
```javascript
// Buka console dan ketik:
window.addTeamMemberRow
// Harus return: function addTeamMemberRow() { ... }
```

### Issue 2: Tombol Tidak Merespon Klik

**Penyebab:**
- onclick attribute tidak ada
- Fungsi belum loaded saat onclick dipanggil

**Solusi:**
1. Inspect element tombol
2. Cek apakah ada attribute `onclick="addTeamMemberRow()"`
3. Jika tidak ada, berarti HTML tidak terupdate

**Test:**
```javascript
// Cek HTML tombol
const btn = document.getElementById('addTeamMember');
console.log(btn.outerHTML);
// Harus ada: onclick="addTeamMemberRow()"
```

### Issue 3: Error "Cannot read property 'appendChild' of null"

**Penyebab:**
- Container `#teamMembersList` tidak ditemukan

**Solusi:**
1. Cek apakah ID benar
2. Cek apakah element ada di DOM

**Test:**
```javascript
// Cek container
const container = document.getElementById('teamMembersList');
console.log('Container exists:', container !== null);
console.log('Container:', container);
```

### Issue 4: Tombol Hapus Tidak Muncul

**Penyebab:**
- `updateTeamRemoveButtons()` tidak dipanggil
- CSS `display: none` tidak terupdate

**Solusi:**
1. Panggil manual `updateTeamRemoveButtons()`
2. Cek style tombol

**Test:**
```javascript
// Update manual
updateTeamRemoveButtons();

// Cek style semua tombol hapus
document.querySelectorAll('.remove-member').forEach((btn, i) => {
    console.log(`Tombol ${i}:`, btn.style.display);
});
```

## 📋 Checklist Debugging

- [ ] Buka halaman `/kelola-catalog`
- [ ] Buka Developer Tools (F12)
- [ ] Buka tab Console
- [ ] Tidak ada error merah di console
- [ ] Fungsi `addTeamMemberRow` ada (typeof = "function")
- [ ] Fungsi `removeTeamMemberRow` ada (typeof = "function")
- [ ] Fungsi `updateTeamRemoveButtons` ada (typeof = "function")
- [ ] Tombol "Tambah Anggota" ada di DOM
- [ ] Container `#teamMembersList` ada di DOM
- [ ] Tombol punya attribute `onclick="addTeamMemberRow()"`
- [ ] Manual call `addTeamMemberRow()` berhasil
- [ ] Manual click tombol berhasil

## 🧪 Test File

Saya sudah membuat file test sederhana: `test_tombol_team.html`

**Cara Test:**
1. Buka file `test_tombol_team.html` di browser
2. Klik tombol "Tambah Anggota"
3. Klik tombol "Hapus"
4. Lihat apakah berfungsi

**Jika test file berfungsi tapi kelola-catalog tidak:**
- Berarti ada konflik dengan kode lain di kelola-catalog
- Cek apakah ada JavaScript error
- Cek apakah ada CSS yang menyembunyikan tombol

## 📸 Screenshot Console yang Diharapkan

```
Kelola catalog page ready
jQuery version: 3.6.0
Add team member button exists: true
Team members list exists: true
Initial team member count: 2
Remove buttons updated. Total members: 2
```

## 🆘 Jika Masih Tidak Bekerja

Kirim screenshot dari:
1. Browser console (tab Console)
2. Element inspector untuk tombol "Tambah Anggota"
3. Element inspector untuk tombol "Hapus"
4. Network tab (cek apakah ada file JS yang gagal load)

Atau jalankan script ini di console dan kirim hasilnya:

```javascript
console.log('=== DEBUG INFO ===');
console.log('1. Fungsi addTeamMemberRow:', typeof addTeamMemberRow);
console.log('2. Fungsi removeTeamMemberRow:', typeof removeTeamMemberRow);
console.log('3. Fungsi updateTeamRemoveButtons:', typeof updateTeamRemoveButtons);
console.log('4. Tombol tambah exists:', document.getElementById('addTeamMember') !== null);
console.log('5. Container exists:', document.getElementById('teamMembersList') !== null);
console.log('6. Jumlah anggota:', document.querySelectorAll('.team-member-item').length);
console.log('7. Tombol tambah onclick:', document.getElementById('addTeamMember')?.getAttribute('onclick'));
console.log('8. Tombol hapus onclick:', document.querySelector('.remove-member')?.getAttribute('onclick'));
console.log('9. jQuery loaded:', typeof $ !== 'undefined');
console.log('10. SweetAlert loaded:', typeof Swal !== 'undefined');
console.log('=== END DEBUG INFO ===');
```
