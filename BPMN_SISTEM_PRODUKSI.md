# BPMN Sistem Pengelolaan Produksi

## Deskripsi Sistem
Sistem pengelolaan produksi ini menangani proses produksi barang jadi dari bahan baku dengan menggunakan metode **Process Costing** dan sistem **FIFO (First In First Out)** untuk pengelolaan stok.

## Komponen Utama Sistem

### 1. **Bill of Materials (BOM)**
- Resep produksi yang berisi daftar bahan baku yang dibutuhkan
- Perhitungan biaya: Bahan Baku + BTKL (60%) + BOP (40%)
- Setiap produk harus memiliki BOM sebelum bisa diproduksi

### 2. **Validasi Stok (FIFO)**
- Sistem mengecek ketersediaan stok bahan baku
- Menggunakan metode FIFO untuk konsumsi bahan baku
- Konversi satuan otomatis (misal: kg ke gram)

### 3. **Perhitungan Biaya Produksi**
- **Total Bahan Baku**: Jumlah x Harga Satuan (dari FIFO)
- **BTKL (Biaya Tenaga Kerja Langsung)**: 60% dari total bahan atau nilai default per unit
- **BOP (Biaya Overhead Pabrik)**: 40% dari total bahan atau nilai default per unit
- **Total Biaya**: Bahan + BTKL + BOP
- **Unit Cost**: Total Biaya / Qty Produksi

### 4. **Pencatatan Jurnal Akuntansi**
Sistem otomatis membuat 3 jurnal:

**Jurnal 1 - Konsumsi Bahan Baku:**
```
Dr. Work In Process (WIP) - 1105
    Cr. Persediaan Bahan Baku - 1104
```

**Jurnal 2 - BTKL & BOP:**
```
Dr. Work In Process (WIP) - 1105
    Cr. Hutang Gaji - 2103
    Cr. Hutang BOP - 2104
```

**Jurnal 3 - Selesai Produksi:**
```
Dr. Persediaan Barang Jadi - 1107
    Cr. Work In Process (WIP) - 1105
```

## Alur Proses BPMN

### Lane 1: Staff Produksi
1. **Mulai Produksi** → Staff memulai proses produksi
2. **Pilih Produk** → Memilih produk yang akan diproduksi (hanya produk dengan BOM)
3. **Input Data** → Memasukkan tanggal dan qty produksi
4. **Lihat Detail** → Melihat hasil produksi setelah selesai
5. **Selesai** → Proses selesai

### Lane 2: Sistem (Validasi & Perhitungan)
1. **Cek BOM** → Validasi apakah produk memiliki BOM
   - **Tidak**: Tampilkan error "BOM belum ada"
   - **Ya**: Lanjut ke validasi stok

2. **Validasi Stok** → Cek ketersediaan stok bahan baku (FIFO)
   - Konversi satuan otomatis
   - Cek setiap bahan baku dalam BOM

3. **Cek Stok Cukup?**
   - **Tidak**: Tampilkan error "Stok tidak cukup" dengan detail bahan yang kurang
   - **Ya**: Lanjut ke perhitungan biaya

4. **Hitung Biaya** → Menghitung total biaya produksi
   - Bahan baku (dari FIFO cost)
   - BTKL (60% atau default)
   - BOP (40% atau default)

### Lane 3: Database & Sistem Akuntansi
1. **Kurangi Stok Bahan** → FIFO consume untuk setiap bahan baku
2. **Tambah Stok Produk** → Add layer untuk produk jadi dengan unit cost
3. **Posting Jurnal 1** → Konsumsi bahan ke WIP
4. **Posting Jurnal 2** → BTKL & BOP ke WIP
5. **Posting Jurnal 3** → Transfer WIP ke Barang Jadi
6. **Simpan Data** → Simpan header produksi dan detail

## Fitur Keamanan & Validasi

### Validasi Input
- Produk harus memiliki BOM dengan detail
- Qty produksi harus > 0
- Tanggal produksi harus valid

### Validasi Stok
- Stok bahan baku harus mencukupi untuk semua item dalam BOM
- Sistem menampilkan pesan error spesifik jika stok kurang
- Format: "Bahan baku [nama1], [nama2] kurang untuk melakukan produksi produk"

### Transaksi Database
- Semua proses menggunakan **DB Transaction**
- Jika ada error, semua perubahan di-rollback
- Konsistensi data terjaga

## Model Data

### Tabel: produksis
```
- id
- produk_id (FK)
- tanggal
- qty_produksi
- total_bahan
- total_btkl
- total_bop
- total_biaya
- status (default: 'pending')
- catatan
- timestamps
```

### Tabel: produksi_details
```
- id
- produksi_id (FK)
- bahan_baku_id (FK)
- qty_resep (qty dalam satuan resep)
- satuan_resep
- qty_konversi (qty dalam satuan dasar)
- harga_satuan
- subtotal
- timestamps
```

### Tabel: stock_layers (FIFO)
```
- id
- item_type ('material' / 'product')
- item_id
- qty
- unit
- unit_cost
- ref_type ('production', 'purchase', dll)
- ref_id
- transaction_date
- timestamps
```

## Cara Menggunakan BPMN

### 1. Buka di Draw.io
- Buka file `BPMN_SISTEM_PRODUKSI.drawio`
- Gunakan draw.io desktop atau online (https://app.diagrams.net)

### 2. Export ke Format Lain
- **PNG/JPG**: File → Export as → PNG/JPEG
- **PDF**: File → Export as → PDF
- **SVG**: File → Export as → SVG

### 3. Edit Diagram
- Klik elemen untuk mengedit
- Drag & drop untuk memindahkan
- Gunakan toolbar untuk menambah elemen baru

## Kode Referensi

### Controller
- `app/Http/Controllers/ProduksiController.php`

### Models
- `app/Models/Produksi.php`
- `app/Models/ProduksiDetail.php`
- `app/Models/Bom.php`
- `app/Models/BomDetail.php`

### Services
- `app/Services/StockService.php` (FIFO management)
- `app/Services/JournalService.php` (Posting jurnal)

### Routes
- `routes/web.php` (prefix: `/transaksi/produksi`)

## Metode Akuntansi

### Process Costing
Sistem menggunakan metode **Process Costing** untuk menghitung HPP (Harga Pokok Produksi):

```
HPP = Bahan Baku + BTKL + BOP
```

### FIFO (First In First Out)
- Bahan baku yang masuk pertama akan digunakan pertama
- Setiap pembelian bahan baku membuat layer baru
- Saat produksi, sistem consume dari layer paling lama
- Unit cost dihitung dari FIFO cost

## Status Produksi
- **pending**: Produksi baru dibuat (default)
- **completed**: Produksi sudah selesai (manual marking)

## Fitur Tambahan

### 1. Hapus Produksi
- Menghapus jurnal terkait
- Menghapus detail produksi
- **Catatan**: Stok tidak dikembalikan (by design)

### 2. Tandai Selesai
- Mengubah status dari 'pending' ke 'completed'
- Untuk tracking produksi yang sudah selesai

### 3. Lihat Detail
- Menampilkan semua bahan baku yang digunakan
- Menampilkan perhitungan biaya lengkap
- Menampilkan qty dalam satuan resep dan satuan dasar

## Tips Penggunaan

1. **Pastikan BOM sudah dibuat** sebelum produksi
2. **Cek stok bahan baku** sebelum input qty besar
3. **Gunakan tanggal yang benar** untuk laporan akurat
4. **Review detail produksi** sebelum ditandai selesai
5. **Backup data** sebelum hapus produksi

## Troubleshooting

### Error: "BOM belum ada"
**Solusi**: Buat BOM untuk produk tersebut di menu Master Data → Bill of Materials

### Error: "Stok tidak cukup"
**Solusi**: 
- Cek stok bahan baku di menu Master Data → Bahan Baku
- Lakukan pembelian bahan baku jika perlu
- Kurangi qty produksi

### Jurnal tidak muncul
**Solusi**: Cek di menu Akuntansi → Jurnal Umum dengan filter tanggal produksi

---

**Dibuat**: 14 November 2025  
**Sistem**: UMKM Center of Excellence - Sistem Akuntansi & Produksi
