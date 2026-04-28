# Test Tombol Tambah & Hapus Anggota Tim

## Persiapan
1. Buka browser ke `/kelola-catalog`
2. Scroll ke section "Team Section"
3. Buka browser console (F12) untuk melihat log

## Test Case 1: Tombol Tambah Anggota

### Langkah:
1. Lihat jumlah anggota tim saat ini (default: 2 anggota)
2. Klik tombol "Tambah Anggota" (hijau dengan icon +)
3. Perhatikan perubahan

### Expected Result:
- ✅ Console log: "Add team member button clicked"
- ✅ Console log: "Team member added. New count: 3"
- ✅ Kolom anggota baru muncul di bawah
- ✅ Kolom baru memiliki:
  - Input file untuk foto
  - Input nama lengkap (kosong)
  - Input jabatan (kosong)
  - Textarea deskripsi (kosong)
  - Tombol hapus (tong sampah merah)
- ✅ Notifikasi sukses muncul: "Anggota tim baru ditambahkan"

### Cara Verifikasi:
```javascript
// Di browser console
$('.team-member-item').length  // Harus bertambah 1
```

---

## Test Case 2: Tombol Hapus Anggota (Ada Lebih dari 1 Anggota)

### Langkah:
1. Pastikan ada minimal 2 anggota tim
2. Klik tombol tong sampah merah pada salah satu anggota
3. Perhatikan dialog konfirmasi

### Expected Result:
- ✅ Console log: "Remove member button clicked"
- ✅ Console log: "Current team member count: X"
- ✅ Dialog konfirmasi muncul:
  - Title: "Hapus Anggota?"
  - Text: "Apakah Anda yakin ingin menghapus [Nama]?"
  - Tombol: "Ya, Hapus!" dan "Batal"
- ✅ Jika klik "Ya, Hapus!":
  - Kolom anggota hilang
  - Console log: "Team member removed. New count: X"
  - Notifikasi: "Anggota tim berhasil dihapus"
- ✅ Jika klik "Batal":
  - Kolom anggota tetap ada
  - Tidak ada perubahan

---

## Test Case 3: Tombol Hapus Anggota (Hanya 1 Anggota Tersisa)

### Langkah:
1. Hapus anggota sampai hanya tersisa 1
2. Klik tombol tong sampah merah pada anggota terakhir

### Expected Result:
- ✅ Console log: "Remove member button clicked"
- ✅ Console log: "Current team member count: 1"
- ✅ Dialog peringatan muncul:
  - Icon: warning
  - Title: "Peringatan"
  - Text: "Minimal harus ada satu anggota tim"
- ✅ Anggota tidak terhapus

---

## Test Case 4: Tambah Multiple Anggota

### Langkah:
1. Klik tombol "Tambah Anggota" 5 kali berturut-turut
2. Perhatikan jumlah anggota

### Expected Result:
- ✅ Setiap klik menambah 1 anggota baru
- ✅ Total anggota bertambah 5
- ✅ Semua kolom baru berfungsi normal
- ✅ Setiap kolom punya tombol hapus sendiri

---

## Test Case 5: Isi Data Anggota Baru

### Langkah:
1. Tambah anggota baru
2. Isi nama: "Test User"
3. Isi jabatan: "Test Position"
4. Isi deskripsi: "Test description"
5. Upload foto
6. Klik "Update Semua Data"

### Expected Result:
- ✅ Semua data tersimpan
- ✅ Foto terupload
- ✅ Data muncul di database

---

## Debugging

### Jika Tombol Tidak Berfungsi:

1. **Cek Console Log**
   ```javascript
   // Buka browser console (F12)
   // Cek apakah ada error
   
   // Test jQuery loaded
   console.log('jQuery:', typeof $);  // Harus: "function"
   
   // Test button exists
   console.log('Button:', $('#addTeamMember').length);  // Harus: 1
   
   // Test list exists
   console.log('List:', $('#teamMembersList').length);  // Harus: 1
   
   // Test member count
   console.log('Members:', $('.team-member-item').length);  // Harus: >= 1
   ```

2. **Cek Event Listener**
   ```javascript
   // Test click event
   $('#addTeamMember').trigger('click');
   // Harus muncul log dan anggota baru
   ```

3. **Cek SweetAlert2**
   ```javascript
   // Test SweetAlert2 loaded
   console.log('Swal:', typeof Swal);  // Harus: "function"
   ```

4. **Manual Test**
   ```javascript
   // Tambah anggota manual
   const memberHtml = `<div class="team-member-item">Test</div>`;
   $('#teamMembersList').append(memberHtml);
   console.log('Count:', $('.team-member-item').length);
   ```

---

## Common Issues

### Issue 1: Tombol tidak merespon klik
**Penyebab:**
- jQuery belum loaded
- Event listener belum terpasang
- Button ID salah

**Solusi:**
```javascript
// Pastikan jQuery loaded
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

// Pastikan kode dalam $(document).ready()
$(document).ready(function() {
    $('#addTeamMember').on('click', function() {
        console.log('Clicked!');
    });
});
```

### Issue 2: Anggota tidak bertambah
**Penyebab:**
- Selector salah
- HTML template error
- JavaScript error

**Solusi:**
- Cek console untuk error
- Cek selector: `$('#teamMembersList')`
- Cek HTML template valid

### Issue 3: Tombol hapus tidak berfungsi
**Penyebab:**
- Event delegation tidak bekerja
- Selector salah

**Solusi:**
```javascript
// Gunakan event delegation
$(document).on('click', '.remove-member', function() {
    console.log('Remove clicked');
});
```

### Issue 4: Notifikasi tidak muncul
**Penyebab:**
- SweetAlert2 belum loaded

**Solusi:**
```html
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

---

## Checklist Akhir

- [ ] Tombol "Tambah Anggota" berfungsi
- [ ] Kolom anggota baru muncul
- [ ] Tombol hapus berfungsi
- [ ] Dialog konfirmasi muncul
- [ ] Minimal 1 anggota tidak bisa dihapus
- [ ] Notifikasi sukses muncul
- [ ] Console log muncul
- [ ] Tidak ada error di console
- [ ] Semua input berfungsi
- [ ] Upload foto berfungsi

---

## Expected Console Output

```
Kelola catalog page ready
jQuery version: 3.6.0
Add team member button exists: true
Team members list exists: true
Initial team member count: 2

[User clicks "Tambah Anggota"]
Add team member button clicked
Team member added. New count: 3

[User clicks tombol hapus]
Remove member button clicked
Current team member count: 3

[User confirms delete]
Team member removed. New count: 2
```

---

**Status Test**: _________________
**Tanggal**: _________________
**Browser**: _________________
**Hasil**: PASS / FAIL
**Catatan**: _________________
