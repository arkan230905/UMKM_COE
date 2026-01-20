# Cara Kerja Sistem Biaya Bahan

## Fitur Utama

### 1. Auto-Fill Harga Satuan
Ketika Anda memilih bahan baku atau bahan pendukung di form edit/create:
- **Harga satuan otomatis muncul** dari data master bahan baku/pendukung
- **Satuan otomatis terisi** sesuai satuan bahan yang dipilih
- Contoh: Pilih "Ayam Kampung" → Harga Rp 50.000/kg otomatis muncul

### 2. Auto-Calculate Subtotal dengan Konversi Satuan
Sistem akan otomatis menghitung subtotal dengan konversi satuan:

#### Contoh Perhitungan:

**Kasus 1: Satuan Sama**
- Bahan: Ayam Kampung (Rp 50.000/kg)
- Input: 2 kg
- Subtotal: 2 × Rp 50.000 = **Rp 100.000**

**Kasus 2: Konversi kg → g**
- Bahan: Ayam Kampung (Rp 50.000/kg)
- Input: 500 g
- Konversi: 500g = 0.5kg
- Subtotal: 0.5 × Rp 50.000 = **Rp 25.000**

**Kasus 3: Konversi g → kg**
- Bahan: Tepung (Rp 10/g)
- Input: 2 kg
- Konversi: 2kg = 2000g
- Subtotal: 2000 × Rp 10 = **Rp 20.000**

**Kasus 4: Konversi liter → ml**
- Bahan: Minyak (Rp 20.000/liter)
- Input: 250 ml
- Konversi: 250ml = 0.25 liter
- Subtotal: 0.25 × Rp 20.000 = **Rp 5.000**

## Konversi Satuan yang Didukung

### Berat (Weight)
- **kg ↔ g**: 1 kg = 1000 g
- **kg ↔ ton**: 1 ton = 1000 kg
- **kilogram ↔ gram**: Sama dengan kg ↔ g

### Volume
- **liter ↔ ml**: 1 liter = 1000 ml
- **l ↔ ml**: Sama dengan liter ↔ ml
- **kl ↔ liter**: 1 kl = 1000 liter

### Unit/Pieces (1:1, tidak ada konversi)
- pcs, unit, pieces, buah → Semua dianggap sama

## Cara Menggunakan

### Di Halaman Edit Biaya Bahan:

1. **Pilih Bahan Baku/Pendukung**
   - Klik dropdown "Pilih Bahan Baku" atau "Pilih Bahan Pendukung"
   - Pilih bahan yang diinginkan
   - ✅ Harga satuan otomatis muncul
   - ✅ Satuan otomatis terisi

2. **Input Jumlah (Qty)**
   - Masukkan jumlah yang dibutuhkan
   - Contoh: 500, 1.5, 2000

3. **Pilih Satuan**
   - Pilih satuan yang Anda inginkan
   - Bisa sama atau berbeda dengan satuan bahan
   - ✅ Subtotal otomatis dihitung dengan konversi

4. **Lihat Hasil**
   - Subtotal per bahan otomatis muncul
   - Total Bahan Baku dihitung otomatis
   - Total Bahan Pendukung dihitung otomatis
   - **Total Biaya Bahan** = Total Bahan Baku + Total Bahan Pendukung

5. **Simpan**
   - Klik tombol "Simpan Perubahan"
   - Data tersimpan ke database
   - harga_bom produk otomatis terupdate

## Contoh Lengkap

### Produk: Nasi Ayam Crispy

**Bahan Baku:**
1. Ayam Kampung
   - Harga: Rp 50.000/kg
   - Input: 500 g
   - Subtotal: Rp 25.000

2. Tepung Terigu
   - Harga: Rp 15.000/kg
   - Input: 0.2 kg
   - Subtotal: Rp 3.000

**Total Bahan Baku: Rp 28.000**

**Bahan Pendukung:**
1. Gas
   - Harga: Rp 100/g
   - Input: 50 g
   - Subtotal: Rp 5.000

2. Plastik Kemasan
   - Harga: Rp 500/pcs
   - Input: 1 pcs
   - Subtotal: Rp 500

**Total Bahan Pendukung: Rp 5.500**

**TOTAL BIAYA BAHAN: Rp 33.500**

## Tips Penggunaan

1. **Pastikan harga satuan di master bahan sudah benar** sebelum menghitung biaya bahan
2. **Gunakan satuan yang konsisten** untuk memudahkan perhitungan
3. **Cek subtotal** setelah input untuk memastikan perhitungan benar
4. **Gunakan tombol "Hitung Ulang Semua"** jika ada perubahan harga bahan baku/pendukung
5. **Simpan perubahan** setelah selesai edit

## Troubleshooting

### Subtotal tidak muncul?
- Pastikan bahan sudah dipilih
- Pastikan qty sudah diisi
- Pastikan satuan sudah dipilih

### Subtotal salah?
- Cek harga satuan di master bahan baku/pendukung
- Cek konversi satuan (lihat tabel konversi di atas)
- Refresh halaman dan coba lagi

### Tidak bisa simpan?
- Pastikan minimal ada 1 bahan baku
- Pastikan semua field required terisi
- Cek console browser untuk error

## Alur Data

```
1. User pilih bahan → Ambil harga_satuan & satuan dari master
2. User input qty → Simpan qty
3. User pilih satuan → Trigger konversi
4. JavaScript hitung: qty × rate × harga_satuan = subtotal
5. Update display subtotal
6. Hitung total semua bahan
7. Update ringkasan
8. User klik simpan → Kirim ke server
9. Server simpan ke database
10. Server update harga_bom produk
```

## Database Schema

### Bahan Baku → BomDetail
- bom_id
- bahan_baku_id
- jumlah (qty yang diinput)
- satuan (satuan yang dipilih)
- harga_per_satuan (dari master bahan baku)
- total_harga (subtotal)

### Bahan Pendukung → BomJobBahanPendukung
- bom_job_costing_id
- bahan_pendukung_id
- jumlah (qty yang diinput)
- satuan (satuan yang dipilih)
- harga_satuan (dari master bahan pendukung)
- subtotal

### Produk
- harga_bom (total biaya bahan, auto-update)
