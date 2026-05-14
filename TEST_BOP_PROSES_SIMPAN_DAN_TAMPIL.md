# Test BOP Proses - Simpan dan Tampil Data

## Checklist Verifikasi

### 1. Form Input (Modal Tambah BOP Proses)
- [x] Input "Nama BOP Proses" tersedia
- [x] Tabel komponen BOP tersedia
- [x] Kolom: Komponen, Rp/produk, Keterangan, Aksi
- [x] Tombol "Tambah Komponen" berfungsi
- [x] Total BOP/produk dihitung otomatis

### 2. Data yang Disimpan ke Database
Ketika form disubmit, data berikut disimpan:
```php
[
    'nama_bop_proses' => 'Nama yang diinput',
    'komponen_bop' => [
        [
            'component' => 'Nama Komponen 1',
            'rate_per_hour' => 1000,
            'description' => 'Keterangan 1'
        ],
        [
            'component' => 'Nama Komponen 2',
            'rate_per_hour' => 2000,
            'description' => 'Keterangan 2'
        ]
    ],
    'total_bop_per_jam' => 3000, // Sum dari semua rate_per_hour
    'bop_per_unit' => 3000, // Sama dengan total_bop_per_jam
    'kapasitas_per_jam' => 1,
    'periode' => '2026-04',
    'keterangan' => 'Keterangan tambahan',
    'is_active' => true
]
```

### 3. Data yang Ditampilkan di Tabel
Kolom tabel:
1. **No** - Nomor urut
2. **Nama BOP Proses** - Dari field `nama_bop_proses`
3. **Total BOP/produk** - Dari field `bop_per_unit` (format: Rp X.XXX)
4. **Jumlah Komponen** - Count dari array `komponen_bop`
5. **Aksi** - Tombol Lihat, Edit, Hapus

### 4. Format Angka
- Menggunakan fungsi `formatNumberClean()` yang:
  - Menghilangkan desimal jika nilai integer (5000 → "5.000")
  - Menampilkan desimal jika ada (5000.50 → "5.000,50")
  - Format: titik untuk ribuan, koma untuk desimal

## Test Case

### Test 1: Input Sederhana
**Input:**
- Nama BOP Proses: "Pertumbuhan"
- Komponen 1: Listrik, Rp 1.000, Keterangan: "Biaya listrik"
- Komponen 2: Air, Rp 500, Keterangan: "Biaya air"

**Expected Output di Tabel:**
- Nama: Pertumbuhan
- Total BOP/produk: Rp 1.500
- Jumlah Komponen: 2 komponen

### Test 2: Input dengan Banyak Komponen
**Input:**
- Nama BOP Proses: "Panen"
- Komponen 1: Tenaga Kerja, Rp 5.000
- Komponen 2: Peralatan, Rp 2.000
- Komponen 3: Transportasi, Rp 3.000
- Komponen 4: Lain-lain, Rp 1.000

**Expected Output di Tabel:**
- Nama: Panen
- Total BOP/produk: Rp 11.000
- Jumlah Komponen: 4 komponen

### Test 3: Input dengan Desimal
**Input:**
- Nama BOP Proses: "Sortir"
- Komponen 1: Bahan Kimia, Rp 1.250,50

**Expected Output di Tabel:**
- Nama: Sortir
- Total BOP/produk: Rp 1.250,50
- Jumlah Komponen: 1 komponen

## Verifikasi Database

Untuk memverifikasi data tersimpan dengan benar, jalankan query:

```sql
SELECT 
    id,
    nama_bop_proses,
    komponen_bop,
    bop_per_unit,
    total_bop_per_jam,
    is_active
FROM bop_proses
ORDER BY id DESC
LIMIT 5;
```

## Troubleshooting

### Masalah: Data tidak tersimpan
**Solusi:**
1. Cek console browser untuk error JavaScript
2. Cek Laravel log: `storage/logs/laravel.log`
3. Pastikan kolom `nama_bop_proses` ada di tabel `bop_proses`

### Masalah: Data tersimpan tapi tidak tampil
**Solusi:**
1. Cek apakah `is_active = 1`
2. Refresh halaman dengan Ctrl+F5
3. Cek query di controller: `where('is_active', true)`

### Masalah: Format angka salah
**Solusi:**
1. Cek fungsi `formatNumberClean()` di view
2. Pastikan `bop_per_unit` bertipe numeric di database
3. Cek casting di model BopProses

## Status Implementasi

✅ Controller menyimpan data dengan benar
✅ Model BopProses memiliki fillable yang lengkap
✅ View menampilkan data dari field yang benar
✅ Format angka menggunakan formatNumberClean()
✅ Komponen BOP disimpan sebagai JSON array
✅ Total BOP dihitung otomatis

## Kesimpulan

Sistem sudah dikonfigurasi untuk:
1. ✅ Menerima input nama BOP proses (text bebas)
2. ✅ Menerima multiple komponen BOP
3. ✅ Menghitung total BOP otomatis
4. ✅ Menyimpan data ke database dengan benar
5. ✅ Menampilkan data sesuai input 100%

Silakan test dengan menambahkan BOP Proses baru dan verifikasi hasilnya!
