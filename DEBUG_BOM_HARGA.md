# Debug BOM - Harga Satuan dan Subtotal Tidak Muncul

## Masalah
Setelah memilih bahan baku, jumlah, dan satuan, kolom "Harga Satuan" dan "Subtotal" tetap kosong.

## Cara Debug

### 1. Buka Console Browser
1. Tekan **F12** atau klik kanan → Inspect
2. Pilih tab **Console**
3. Refresh halaman (Ctrl+F5)

### 2. Periksa Log Saat Halaman Load
Seharusnya muncul:
```
DOM loaded, initializing...
Attaching change event to bahanSelect
Attaching input event to jumlahInput
Attaching change event to satuanSelect
Initialization complete
Bahan Baku Data: [...]
Satuan Data: [...]
```

### 3. Pilih Bahan Baku
Seharusnya muncul:
```
Bahan baku changed: 6
updateHargaSatuan called
Selected option: <option>
Selected value: 6
Harga per KG: 50000
Satuan utama: KG
Nama bahan: Ayam Kampung
Calling hitungSubtotal
```

### 4. Ubah Jumlah atau Satuan
Seharusnya muncul:
```
Jumlah changed: 100
hitungSubtotal called
Jumlah: 100
Satuan: G
Harga per KG: 50000
Satuan utama: KG
Jumlah dalam KG: 0.1
Faktor konversi: 0.001
Harga per satuan: 50
Subtotal: 5000
Display updated, calling hitungTotal
```

## Kemungkinan Masalah

### A. Event Listener Tidak Terpasang
**Gejala**: Tidak ada log "Bahan baku changed" saat pilih bahan baku

**Penyebab**:
- Element tidak ditemukan
- JavaScript error sebelum event listener terpasang

**Solusi**:
1. Periksa apakah ada error di console
2. Pastikan element dengan class `.bahan-select`, `.jumlah-input`, `.satuan-select` ada

### B. Data Harga Tidak Ada
**Gejala**: Log "Harga per KG: 0" atau "Harga per KG is 0, resetting"

**Penyebab**:
- Bahan baku belum pernah dibeli (harga_satuan = 0 atau NULL)
- Data attribute `data-harga` tidak ada di option

**Solusi**:
1. Periksa database: `SELECT id, nama_bahan, harga_satuan FROM bahan_bakus;`
2. Pastikan bahan baku sudah pernah dibeli
3. Periksa HTML option: `<option data-harga="50000">`

### C. Konversi Satuan Salah
**Gejala**: Subtotal = 0 atau NaN

**Penyebab**:
- Satuan tidak ada di object `konversiKeKg`
- Faktor konversi salah

**Solusi**:
1. Periksa log "Faktor konversi"
2. Tambahkan satuan baru ke object `konversiKeKg` jika perlu

### D. Element Display Tidak Ditemukan
**Gejala**: Error "Cannot set property 'textContent' of null"

**Penyebab**:
- Element `.harga-display`, `.harga-info`, atau `.subtotal-display` tidak ada

**Solusi**:
1. Periksa HTML struktur tabel
2. Pastikan div dengan class tersebut ada di setiap baris

## Testing Manual

### Test 1: Pilih Bahan Baku
1. Pilih "Ayam Kampung" dari dropdown
2. **Expected**: 
   - Satuan otomatis berubah ke "KG"
   - Harga satuan muncul: "Rp 50.000"
   - Subtotal muncul: "Rp 50.000" (jika jumlah = 1)

### Test 2: Ubah Jumlah
1. Ubah jumlah menjadi 100
2. **Expected**:
   - Subtotal update: "Rp 5.000.000" (jika satuan = KG)

### Test 3: Ubah Satuan
1. Ubah satuan menjadi "G"
2. **Expected**:
   - Harga satuan update: "Rp 50"
   - Keterangan muncul: "(1 kg = Rp 50.000 / 1000g)"
   - Subtotal update: "Rp 5.000" (100g × Rp 50)

### Test 4: Tambah Baris Baru
1. Klik "Tambah Baris"
2. Pilih bahan baku di baris baru
3. **Expected**: Harga dan subtotal muncul di baris baru

## Solusi Cepat

Jika masih tidak berfungsi setelah debug:

1. **Clear Cache Browser**: Ctrl+Shift+Delete
2. **Hard Refresh**: Ctrl+F5
3. **Periksa Network Tab**: Pastikan file JS ter-load
4. **Disable Browser Extensions**: Mungkin ada extension yang block JavaScript

## Kontak Support

Jika masih bermasalah, kirim screenshot:
1. Console log (F12 → Console)
2. Network tab (F12 → Network)
3. HTML inspect element (klik kanan pada dropdown → Inspect)
