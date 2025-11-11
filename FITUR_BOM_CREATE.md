# Fitur Form Create BOM

## Tampilan Sesuai Gambar

Form create BOM telah dibuat dengan tampilan dan fitur yang persis seperti gambar referensi:

### 1. Header
- Tombol "Kembali" untuk kembali ke index
- Judul "Bill of Materials (BOM)"
- Tombol "Buat BOM Baru" di kanan atas

### 2. Pilih Produk
- Dropdown untuk memilih produk
- Menampilkan kode produk di bawah dropdown
- Background berwarna abu-abu muda dengan label "NAMA PRODUK" berwarna ungu

### 3. Tabel Bahan Baku
Header tabel dengan background ungu (#6366f1):
- **BAHAN BAKU**: Dropdown untuk memilih bahan baku
- **JUMLAH**: Input number untuk jumlah
- **SATUAN**: Dropdown untuk memilih satuan (KG, G, HG, dll)
- **HARGA SATUAN (RP)**: Menampilkan harga per satuan yang dipilih
- **SUBTOTAL (RP)**: Menampilkan subtotal otomatis
- **AKSI**: Tombol edit (kuning) dan hapus (merah)

### 4. Keterangan Konversi Satuan
Di bawah harga satuan ditampilkan keterangan konversi, contoh:
- `(1 kg = Rp 22.200 / 1000g)` - untuk satuan gram
- Otomatis menyesuaikan dengan satuan yang dipilih

### 5. Footer Tabel (Perhitungan)
- **Total Bahan Baku**: Sum dari semua subtotal
- **BTKL (12%)**: 12% dari total bahan baku
- **BOP (26% of BTKL)**: 26% dari BTKL
- **Total HPP**: Total keseluruhan (Bahan Baku + BTKL + BOP)

### 6. Tombol Aksi
- **Simpan BOM**: Menyimpan data BOM
- **Batal**: Kembali ke halaman index

## Logika Konversi Satuan

### Faktor Konversi ke KG
```javascript
{
    'KG': 1,
    'HG': 0.1,
    'DAG': 0.01,
    'G': 0.001,
    'GR': 0.001,
    'GRAM': 0.001,
    'ONS': 0.1,
    'KW': 100,
    'TON': 1000,
    'PCS': 1,
    'UNIT': 1,
    'BUAH': 1,
    'BTL': 1,
    'LITER': 1,
    'L': 1,
    'ML': 0.001
}
```

### Cara Kerja Konversi

1. **Harga Dasar**: Harga bahan baku disimpan dalam satuan utama (biasanya KG)
2. **Konversi Harga**: 
   - Harga per satuan = Harga per KG × Faktor konversi
   - Contoh: Jika harga per KG = Rp 22.200
     - Harga per G = Rp 22.200 × 0.001 = Rp 22,2
3. **Perhitungan Subtotal**:
   - Jumlah dikonversi ke KG terlebih dahulu
   - Subtotal = Harga per KG × Jumlah dalam KG
   - Contoh: 100 G × Rp 22.200/KG = 0.1 KG × Rp 22.200 = Rp 2.220

### Contoh Perhitungan

**Bahan: Ayam I Kolong**
- Harga per KG: Rp 22.200
- Jumlah: 100 G
- Konversi: 100 G = 0.1 KG
- Subtotal: 0.1 × Rp 22.200 = Rp 2.220
- Keterangan: "(1 kg = Rp 22.200 / 1000g)"

**Bahan: Tepung Terigu**
- Harga per KG: Rp 12.000
- Jumlah: 20 G
- Konversi: 20 G = 0.02 KG
- Subtotal: 0.02 × Rp 12.000 = Rp 240
- Keterangan: "(1 kg = Rp 12.000 / 1000g)"

## Fitur JavaScript

### 1. Tambah Baris Dinamis
- Klik tombol "Tambah Baris" untuk menambah bahan baku baru
- Setiap baris memiliki ID unik

### 2. Update Otomatis
- Harga satuan update otomatis saat bahan baku dipilih
- Subtotal update otomatis saat jumlah atau satuan berubah
- Total update otomatis saat ada perubahan

### 3. Validasi
- Minimal harus ada 1 bahan baku
- Semua field harus terisi
- Jumlah harus lebih dari 0
- Produk harus dipilih

### 4. Tombol Aksi Per Baris
- **Edit**: Focus ke baris untuk edit
- **Hapus**: Hapus baris (minimal 1 baris harus tetap ada)

## Format Data yang Dikirim

```php
[
    'produk_id' => 25,
    'bahan_baku_id' => [6, 20],
    'jumlah' => [100, 20],
    'satuan' => ['G', 'G'],
    'kategori' => ['BOP', 'BOP']
]
```

## Styling

- Header tabel: Background ungu (#6366f1), teks putih
- Footer tabel: Background abu-abu muda (#f8f9fa)
- Total HPP: Background biru muda (#e3f2fd)
- Tombol edit: Kuning (warning)
- Tombol hapus: Merah (danger)
- Format rupiah: Rp 2.220 (dengan pemisah ribuan)

## Testing

1. Pilih produk dari dropdown
2. Klik "Tambah Baris"
3. Pilih bahan baku
4. Masukkan jumlah
5. Pilih satuan
6. Lihat harga satuan dan keterangan konversi muncul otomatis
7. Lihat subtotal dihitung otomatis
8. Lihat total di footer update otomatis
9. Klik "Simpan BOM"
