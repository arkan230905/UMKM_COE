# Testing: Tombol Tambah Bahan Baku & Bahan Pendukung

## 🧪 Cara Testing

### Persiapan:
1. Buka browser
2. Buka Developer Console (F12)
3. Pilih tab "Console"
4. Buka halaman: `http://localhost:8000/master-data/biaya-bahan`
5. Cari produk dengan Total Biaya = Rp 0
6. Klik tombol "Tambah" (hijau)

---

## Test 1: Halaman CREATE Terbuka

### Yang Harus Terlihat:
- [ ] Halaman CREATE terbuka
- [ ] Header: "Tambah Biaya Bahan"
- [ ] Warna header: Hijau

### Di Console Harus Muncul:
```
Biaya Bahan Create - Script loaded
DOM loaded, initializing...
Auto-adding first Bahan Baku row...
Add Bahan Baku clicked
Row added: bahanBaku_[timestamp]
Attaching event listeners to: bahanBaku_[timestamp]
Found remove buttons: 1
Found bahan selects: 1
Found qty inputs: 1
Found satuan selects: 1
Updating row numbers...
Bahan Baku rows: 1
Bahan Pendukung rows: 0
=== Initialization Start ===
Attaching event listeners to all elements...
...
=== Initialization Complete ===
Ready to use!
```

### Yang Harus Ada di Tabel:
- [ ] 1 baris Bahan Baku sudah ditambahkan otomatis
- [ ] Nomor urut: 1
- [ ] Dropdown bahan baku bisa diklik
- [ ] Input qty bisa diisi
- [ ] Dropdown satuan bisa diklik
- [ ] Tombol trash (hapus) ada

---

## Test 2: Klik Tombol "Tambah Bahan Baku"

### Langkah:
1. Klik tombol hijau "Tambah Bahan Baku"
2. Lihat console
3. Lihat tabel

### Di Console Harus Muncul:
```
Add Bahan Baku clicked
Row added: bahanBaku_[timestamp]
Attaching event listeners to: bahanBaku_[timestamp]
Found remove buttons: 1
Found bahan selects: 1
Found qty inputs: 1
Found satuan selects: 1
Updating row numbers...
Bahan Baku rows: 2
Bahan Pendukung rows: 0
```

### Yang Harus Terjadi:
- [ ] Baris baru ditambahkan
- [ ] Nomor urut otomatis update (1, 2)
- [ ] Baris baru kosong (tidak ada value)
- [ ] Semua input/select bisa digunakan
- [ ] Tombol trash ada di setiap baris

---

## Test 3: Klik Tombol "Tambah Bahan Pendukung"

### Langkah:
1. Scroll ke bagian Bahan Pendukung
2. Klik tombol kuning "Tambah Bahan Pendukung"
3. Lihat console
4. Lihat tabel

### Di Console Harus Muncul:
```
Add Bahan Pendukung clicked
Row added: bahanPendukung_[timestamp]
Attaching event listeners to: bahanPendukung_[timestamp]
Found remove buttons: 1
Found bahan selects: 1
Found qty inputs: 1
Found satuan selects: 1
Updating row numbers...
Bahan Baku rows: 2
Bahan Pendukung rows: 1
```

### Yang Harus Terjadi:
- [ ] Baris baru ditambahkan di tabel Bahan Pendukung
- [ ] Nomor urut: 1
- [ ] Baris baru kosong
- [ ] Semua input/select bisa digunakan
- [ ] Tombol trash ada

---

## Test 4: Tambah Multiple Baris

### Langkah:
1. Klik "Tambah Bahan Baku" 3 kali
2. Klik "Tambah Bahan Pendukung" 2 kali
3. Lihat console
4. Lihat tabel

### Yang Harus Terjadi:
- [ ] Total Bahan Baku: 4 baris (1 auto + 3 manual)
- [ ] Total Bahan Pendukung: 2 baris
- [ ] Nomor urut Bahan Baku: 1, 2, 3, 4
- [ ] Nomor urut Bahan Pendukung: 1, 2
- [ ] Semua baris berfungsi normal

### Di Console:
```
Bahan Baku rows: 4
Bahan Pendukung rows: 2
```

---

## Test 5: Hapus Baris

### Langkah:
1. Klik tombol trash pada baris ke-2 Bahan Baku
2. Lihat console
3. Lihat tabel

### Di Console Harus Muncul:
```
Remove button clicked
Removing row: bahanBaku_[timestamp]
Updating row numbers...
Bahan Baku rows: 3
```

### Yang Harus Terjadi:
- [ ] Baris ke-2 terhapus
- [ ] Nomor urut otomatis update: 1, 2, 3
- [ ] Total baris: 3

---

## Test 6: Input Data dan Auto-Calculate

### Langkah:
1. Di baris pertama Bahan Baku:
   - Pilih bahan: Ayam Kampung
   - Input qty: 2
   - Pilih satuan: Kilogram
2. Lihat console
3. Lihat subtotal

### Di Console Harus Muncul:
```
Bahan changed: [id]
Auto-selected satuan: Kilogram
Calculating totals...
Qty changed: 2
Calculating totals...
Totals: { baku: [nilai], pendukung: 0, total: [nilai] }
```

### Yang Harus Terjadi:
- [ ] Harga muncul di dropdown text
- [ ] Satuan otomatis terpilih
- [ ] Subtotal otomatis dihitung
- [ ] Total Bahan Baku otomatis update
- [ ] Total Biaya Bahan otomatis update

---

## 🐛 Troubleshooting

### Jika Tombol Tidak Berfungsi:

#### 1. Cek Console untuk Error
```javascript
// Jika muncul error seperti:
"Cannot read property 'click' of null"
"Template row not found"
"addBahanBaku button not found"
```
**Solusi**: Hard refresh `Ctrl + F5`

#### 2. Cek Element ID
Buka Console, ketik:
```javascript
console.log(document.getElementById('addBahanBaku'));
console.log(document.getElementById('addBahanPendukung'));
console.log(document.getElementById('newBahanBakuRow'));
console.log(document.getElementById('newBahanPendukungRow'));
```
**Harus return**: Element (bukan null)

#### 3. Cek Event Listener
Buka Console, ketik:
```javascript
document.getElementById('addBahanBaku').click();
```
**Harus**: Menambah baris baru

#### 4. Cek Template Row
Buka Console, ketik:
```javascript
const row = document.getElementById('newBahanBakuRow');
console.log(row);
console.log(row.classList.contains('d-none'));
```
**Harus**: 
- row = element
- classList contains 'd-none' = true

---

## ✅ Checklist Lengkap

### Tombol Tambah Bahan Baku:
- [ ] Tombol terlihat
- [ ] Tombol bisa diklik
- [ ] Menambah baris baru
- [ ] Baris baru kosong
- [ ] Nomor urut otomatis
- [ ] Event listener terpasang
- [ ] Tidak ada error di console

### Tombol Tambah Bahan Pendukung:
- [ ] Tombol terlihat
- [ ] Tombol bisa diklik
- [ ] Menambah baris baru
- [ ] Baris baru kosong
- [ ] Nomor urut otomatis
- [ ] Event listener terpasang
- [ ] Tidak ada error di console

### Auto-Add First Row:
- [ ] 1 baris Bahan Baku otomatis ditambahkan
- [ ] Terjadi saat halaman load
- [ ] Tidak ada error

### Hapus Baris:
- [ ] Tombol trash berfungsi
- [ ] Baris terhapus
- [ ] Nomor urut update
- [ ] Total update

### Auto-Calculate:
- [ ] Pilih bahan → harga muncul
- [ ] Satuan otomatis terpilih
- [ ] Input qty → subtotal dihitung
- [ ] Total otomatis update

---

## 📝 Log Console yang Benar

Saat halaman CREATE dibuka, console harus menampilkan:

```
Biaya Bahan Create - Script loaded
DOM loaded, initializing...
Auto-adding first Bahan Baku row...
Add Bahan Baku clicked
Row added: bahanBaku_1234567890
Attaching event listeners to: bahanBaku_1234567890
Found remove buttons: 1
Found bahan selects: 1
Found qty inputs: 1
Found satuan selects: 1
Updating row numbers...
Bahan Baku rows: 1
Bahan Pendukung rows: 0
=== Initialization Start ===
Attaching event listeners to all elements...
Attaching event listeners to: all elements
Found remove buttons: 1
Found bahan selects: 1
Found qty inputs: 1
Found satuan selects: 1
Calculating initial totals...
Totals: { baku: 0, pendukung: 0, total: 0 }
=== Initialization Complete ===
Ready to use!
```

---

## 🎯 Expected Behavior

### Saat Klik "Tambah Bahan Baku":
1. Console log: "Add Bahan Baku clicked"
2. Baris baru muncul di tabel
3. Baris baru memiliki ID unik
4. Semua input kosong
5. Event listener terpasang
6. Nomor urut update

### Saat Klik "Tambah Bahan Pendukung":
1. Console log: "Add Bahan Pendukung clicked"
2. Baris baru muncul di tabel
3. Baris baru memiliki ID unik
4. Semua input kosong
5. Event listener terpasang
6. Nomor urut update

---

## 🚨 Red Flags (Tanda Ada Masalah)

### Di Console:
- ❌ "Template row not found"
- ❌ "addBahanBaku button not found"
- ❌ "Cannot read property 'click' of null"
- ❌ "Uncaught TypeError"
- ❌ Tidak ada log sama sekali

### Di UI:
- ❌ Tombol tidak bisa diklik
- ❌ Tidak ada baris yang ditambahkan
- ❌ Baris ditambahkan tapi tidak berfungsi
- ❌ Nomor urut tidak update
- ❌ Event listener tidak terpasang

---

**Jika semua test PASS, tombol berfungsi dengan baik!** ✅
