# Sistem Konversi Satuan Berbasis Produksi Nyata

## 📋 Overview

Sistem konversi satuan ini dirancang untuk mengatasi kompleksitas konversi satuan dalam aplikasi manufaktur job process costing dengan pendekatan **berbasis produksi nyata**. User tidak perlu memahami rumus konversi yang kompleks, sistem akan belajar otomatis dari data produksi yang terjadi.

## 🎯 Prinsip Utama

1. **Konversi tidak dilakukan dengan rumus satuan universal**
2. **Konversi dilakukan berdasarkan hasil produksi nyata**
3. **User hanya menginput data yang memang terjadi di lapangan**
4. **Sistem belajar dan meningkatkan akurasi dari waktu ke waktu**

## 🔄 Alur Sistem

### 1. Pembelian Bahan Baku
- Bahan baku dibeli dalam satuan besar (misalnya: kilogram)
- Sistem menyimpan stok dalam satuan pembelian
- Contoh: Ayam dibeli 5 kg @ Rp40.000/kg = Rp200.000

### 2. Proses Produksi (Titik Konversi)
- Saat produksi, user memasukkan:
  - Jumlah bahan baku yang digunakan
  - Jumlah hasil produksi yang dihasilkan
- Sistem menggunakan data ini sebagai dasar konversi
- Contoh input produksi:
  - Bahan digunakan: 5 kg ayam
  - Hasil produksi: 40 potong ayam
- **Konversi terjadi secara implisit: 1 kg ayam ≈ 8 potong**

### 3. Resep/Pemakaian per Produk
- Produk jadi memiliki resep penggunaan bahan hasil produksi
- User menginput pemakaian dalam satuan yang mudah dipahami
- Contoh resep: Ayam geprek = 2 potong ayam per porsi

### 4. Perhitungan Otomatis oleh Sistem
- Sistem menghitung:
  - Biaya per potong
  - Biaya bahan baku per porsi
  - Pengurangan stok otomatis
- Contoh hasil:
  - Biaya per potong ayam = Rp5.000
  - Biaya ayam per porsi = Rp10.000

## 🗄️ Struktur Database

### Tabel `konversi_produksis`
```sql
CREATE TABLE konversi_produksis (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bahan_baku_id BIGINT NOT NULL,
    produksi_id BIGINT NULL,
    jumlah_bahan_asli DECIMAL(15,4) NOT NULL COMMENT 'Jumlah bahan baku yang digunakan',
    satuan_asli VARCHAR(50) NOT NULL COMMENT 'Satuan pembelian bahan baku',
    jumlah_hasil_produksi DECIMAL(15,4) NOT NULL COMMENT 'Jumlah hasil produksi',
    satuan_hasil VARCHAR(50) NOT NULL COMMENT 'Satuan hasil produksi',
    faktor_konversi DECIMAL(15,8) NOT NULL COMMENT '1 satuan_asli = X satuan_hasil',
    harga_per_satuan_hasil DECIMAL(15,4) NOT NULL COMMENT 'Harga per satuan hasil',
    tanggal_produksi DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    confidence_score DECIMAL(5,4) DEFAULT 1.0 COMMENT 'Skor kepercayaan (0-1)',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 🔧 Komponen Sistem

### 1. Model `KonversiProduksi`
- Menyimpan data konversi dari produksi nyata
- Memiliki relasi ke `BahanBaku` dan `Produksi`
- Method untuk menghitung faktor konversi otomatis
- Confidence scoring untuk validasi konsistensi

### 2. Service `KonversiProduksiService`
- **`simpanKonversiDariProduksi()`** - Otomatis menyimpan konversi dari data produksi
- **`hitungBiayaBahanResep()`** - Menghitung biaya bahan berdasarkan konversi
- **`konversiJumlahResep()`** - Konversi jumlah untuk resep
- **`getRingkasanKonversi()`** - Get ringkasan konversi aktif

### 3. Update Model `BahanBaku`
- **`konversiBerdasarkanProduksi()`** - Konversi menggunakan data produksi
- **`getHargaPerSatuanHasil()`** - Get harga per satuan hasil
- **`getActiveKonversi()`** - Get konversi aktif untuk bahan baku

### 4. Update Service `HargaService`
- **`calculateHPPBasedOnProduction()`** - Hitung HPP berdasarkan konversi nyata
- **`getHargaPerSatuanHasil()`** - Get harga per satuan hasil
- **`updateHargaBasedOnProduction()`** - Update harga berdasarkan produksi

## 📱 Interface User

### Form Produksi Baru
- Input jumlah produksi yang nyata (contoh: 40 potong)
- Preview otomatis kebutuhan bahan baku
- Validasi stok real-time
- Informasi konversi yang akan dipelajari

### Panduan Konversi
- Cara kerja konversi otomatis
- Contoh praktis
- Tujuan sistem (memudahkan user)

## 🚀 Cara Kerja Implementasi

### 1. Saat Produksi Disimpan
```php
// Di ProduksiController@store
$konversiService = new KonversiProduksiService();
$konversiService->simpanKonversiDariProduksi($produksi);
```

### 2. Perhitungan Biaya Bahan
```php
// Menggunakan konversi produksi
$biayaBahan = $konversiService->hitungBiayaBahanResep(
    $bahanBakuId,
    $jumlahResep,
    $satuanResep
);
```

### 3. Konversi Jumlah
```php
// Konversi dari satuan resep ke satuan stok
$jumlahStok = $bahanBaku->konversiBerdasarkanProduksi(
    $jumlah,
    $dariSatuan,
    $keSatuan
);
```

## 📊 Contoh Implementasi

### Kasus: Ayam Geprek

#### 1. Pembelian Awal
```
Bahan: Ayam
Jumlah: 10 kg
Harga: Rp38.000/kg
Total: Rp380.000
```

#### 2. Produksi Pertama
```
Tanggal: 1 Januari 2026
Produk: Ayam Geprek
Jumlah Produksi: 80 potong
Bahan Digunakan: 10 kg ayam
```

#### 3. Konversi Otomatis Tersimpan
```
Faktor Konversi: 1 kg = 8 potong
Harga per Potong: Rp38.000 / 8 = Rp4.750
Confidence Score: 1.0 (pertama kali)
```

#### 4. Resep Produk
```
Ayam Geprek:
- 2 potong ayam
- Bumbu: 50g
- Minyak: 30ml
```

#### 5. Perhitungan HPP Otomatis
```
Biaya Ayam: 2 potong × Rp4.750 = Rp9.500
Biaya Bumbu: Rp500
Biaya Minyak: Rp300
Total HPP: Rp10.300/porsi
```

## 🔄 Update Konversi

### Weighted Average
Sistem menggunakan weighted average untuk update konversi:
```
Konversi Baru = (Konversi_Lama × Total_Lama + Konversi_Baru) / (Total_Lama + 1)
```

### Confidence Scoring
- **1.0** - Konversi pertama atau sangat konsisten
- **0.8-0.9** - Konsisten dengan sedikit variasi
- **0.5-0.7** - Variasi moderat
- **<0.5** - Variasi tinggi, perlu review

## 🛡️ Error Handling & Fallback

### Jika Konversi Tidak Ada
- Fallback ke konversi universal (kg ke gram, dll)
- Log warning untuk monitoring
- User tetap bisa menggunakan sistem

### Jika Stok Tidak Cukup
- Validasi real-time di form
- Tombol submit disabled
- Pesan error yang jelas

## 📈 Monitoring & Maintenance

### Log System
- Semua konversi baru di-log
- Error handling tercatat
- Confidence score tracking

### Report Konversi
- Ringkasan konversi per bahan baku
- Trend confidence score
- Rekomendasi optimalisasi

## 🎯 Keuntungan Sistem

1. **User-Friendly**: Tidak perlu hitung konversi manual
2. **Akurat**: Berdasarkan produksi nyata
3. **Adaptif**: Belajar dan meningkat dari waktu ke waktu
4. **Effisien**: Otomatisasi perhitungan biaya
5. **Traceable**: Semua konversi tercatat dan bisa di-audit

## 🔮 Future Enhancements

1. **Machine Learning**: Prediksi konversi berdasarkan historis
2. **Multi-Stage Conversion**: Untuk proses produksi kompleks
3. **Batch Conversion**: Konversi untuk batch produksi
4. **Mobile App**: Input produksi via mobile
5. **Integration**: ERP system integration

---

## 📞 Support

Untuk bantuan teknis atau pertanyaan tentang sistem konversi:
- Documentation: `/docs/konversi-produksi`
- API Reference: `/api/konversi`
- Support Team: IT Support Team

*Sistem ini dirancang khusus untuk UMKM dengan fokus pada kemudahan penggunaan dan akurasi perhitungan biaya produksi.*
