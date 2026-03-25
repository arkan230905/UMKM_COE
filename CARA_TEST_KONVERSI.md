# Cara Test Sistem Konversi Bertingkat

## Langkah-langkah Testing:

### 1. Buka Form Pembelian
- Akses: http://127.0.0.1:8000/transaksi/pembelian/create
- Pilih vendor (misal: Tel-Mart untuk Bahan Baku)

### 2. Test Bahan Baku
1. **Pilih Bahan Baku**: Ayam Kampung atau Ayam Potong
2. **Isi Jumlah**: 10
3. **Pilih Satuan Pembelian**: Kilogram (KG)
4. **Isi Harga per Satuan**: 48000
5. **Isi Konversi ke Satuan Utama**: 8 (artinya 10 KG = 8 Ekor)

### 3. Hasil yang Diharapkan
Setelah langkah 5, di bagian "Konversi ke Sub Satuan (Manual)" akan muncul:
- Input untuk sub satuan (misal: Potong)
- Rumus otomatis: "8 Ekor × [nilai] Potong/Ekor = [hasil] Potong"

### 4. Test Manual Input
1. **Edit nilai sub satuan**: Ganti dari nilai otomatis ke 32
2. **Lihat rumus berubah**: "8 Ekor × 4 Potong/Ekor = 32 Potong"

## Troubleshooting

### Jika Input Konversi Tidak Muncul:
1. **Pastikan bahan baku memiliki sub satuan yang dikonfigurasi**
2. **Cek console browser** untuk error JavaScript (F12 → Console)
3. **Pastikan semua langkah diikuti berurutan**

### Jika Rumus Tidak Update:
1. **Refresh halaman** dan coba lagi
2. **Pastikan JavaScript tidak diblokir**
3. **Cek apakah ada error di console**

## Contoh Data Test

### Input:
- Bahan: Ayam Kampung
- Jumlah: 10 KG
- Konversi: 8 Ekor
- Sub satuan: 32 Potong (manual)

### Output yang Diharapkan:
```
Konversi ke Sub Satuan (Manual)
= 32 Potong
Rumus: 8 Ekor × 4 Potong/Ekor = 32 Potong
```

## Catatan Penting
- Sistem hanya menampilkan input konversi jika bahan baku/pendukung memiliki sub satuan yang dikonfigurasi di master data
- Jika tidak ada sub satuan, akan tetap menampilkan instruksi saja