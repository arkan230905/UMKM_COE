# BPMN Sistem Saat Ini (As-Is) dan Sistem Usulan (To-Be)

## 1. BPMN Sistem Saat Ini (As-Is) - Proses Produksi Manual

```mermaid
graph TB
    Start([Mulai Produksi]) --> CheckBahan[Admin Cek Stok Bahan<br/>Secara Manual di Buku]
    
    CheckBahan --> CukupManual{Bahan<br/>Cukup?}
    
    CukupManual -->|Tidak| BeliManual[Admin Beli Bahan<br/>Catat di Buku Terpisah]
    BeliManual --> CheckBahan
    
    CukupManual -->|Ya| CatatProduksi[Admin Catat Produksi<br/>di Lembar Kerja Excel]
    
    CatatProduksi --> KurangiManual[Admin Kurangi Stok Bahan<br/>Manual di Buku]
    
    KurangiManual --> HitungManual[Admin Hitung HPP<br/>Manual dengan Kalkulator]
    
    HitungManual --> TambahProduk[Admin Tambah Stok Produk<br/>Manual di Buku Lain]
    
    TambahProduk --> BuatLaporan[Admin Buat Laporan<br/>Manual di Excel]
    
    BuatLaporan --> Arsip[Simpan Dokumen<br/>di File Fisik]
    
    Arsip --> End([Selesai])
    
    style Start fill:#90EE90
    style End fill:#90EE90
    style CukupManual fill:#FFD700
    style CheckBahan fill:#FFB6C1
    style KurangiManual fill:#FFB6C1
    style HitungManual fill:#FFB6C1
    style TambahProduk fill:#FFB6C1
    style BuatLaporan fill:#FFB6C1
```

### Masalah Sistem Saat Ini:

| No | Masalah | Dampak |
|----|---------|--------|
| 1 | Pencatatan terpisah di buku/spreadsheet berbeda | Data tidak terintegrasi, sulit tracking |
| 2 | Pengecekan stok manual | Memakan waktu, sering terlambat |
| 3 | Perhitungan HPP manual | Rawan kesalahan, tidak akurat |
| 4 | Update stok manual | Risiko lupa update, data tidak real-time |
| 5 | Pembuatan laporan manual | Memakan waktu, sulit analisis |
| 6 | Tidak ada validasi otomatis | Kesalahan input tidak terdeteksi |
| 7 | Tidak ada jurnal akuntansi | Laporan keuangan tidak standar |
| 8 | Arsip fisik | Sulit dicari, risiko hilang |

---

## 2. BPMN Sistem Usulan (To-Be) - Proses Produksi dengan UMKM COE

```mermaid
graph TB
    Start([Mulai Produksi]) --> Login[Admin Login<br/>ke Sistem]
    
    Login --> MenuProduksi[Pilih Menu<br/>Transaksi > Produksi]
    
    MenuProduksi --> InputData[Input Data Produksi<br/>- Pilih Produk<br/>- Tanggal<br/>- Qty]
    
    InputData --> ValidasiBOM{Sistem Validasi<br/>BOM Ada?}
    
    ValidasiBOM -->|Tidak| ErrorBOM[Sistem Tampilkan Error:<br/>BOM Belum Ada]
    ErrorBOM --> End1([Selesai])
    
    ValidasiBOM -->|Ya| CekStokAuto{Sistem Cek Stok<br/>Otomatis}
    
    CekStokAuto -->|Tidak Cukup| ErrorStok[Sistem Tampilkan:<br/>Bahan yang Kurang]
    ErrorStok --> End2([Selesai])
    
    CekStokAuto -->|Cukup| ProsesProduksi[Sistem Proses Produksi:<br/>1. Consume Stok FIFO<br/>2. Hitung HPP Otomatis<br/>3. Update Stok Produk<br/>4. Posting 3 Jurnal]
    
    ProsesProduksi --> SimpanDB[(Simpan ke Database)]
    
    SimpanDB --> TampilDetail[Sistem Tampilkan<br/>Detail Produksi]
    
    TampilDetail --> LaporanOtomatis[Laporan Otomatis<br/>Tersedia Real-time]
    
    LaporanOtomatis --> End3([Selesai])
    
    style Start fill:#90EE90
    style End1 fill:#FFB6C1
    style End2 fill:#FFB6C1
    style End3 fill:#90EE90
    style ValidasiBOM fill:#FFD700
    style CekStokAuto fill:#FFD700
    style ErrorBOM fill:#FF6B6B
    style ErrorStok fill:#FF6B6B
    style ProsesProduksi fill:#87CEEB
    style SimpanDB fill:#DDA0DD
    style LaporanOtomatis fill:#98FB98
```

### Keunggulan Sistem Usulan:

| No | Fitur | Manfaat |
|----|-------|---------|
| 1 | Database terpusat | Semua data terintegrasi, mudah diakses |
| 2 | Validasi otomatis | Mencegah kesalahan input |
| 3 | Cek stok real-time | Tahu langsung ketersediaan bahan |
| 4 | Perhitungan HPP otomatis | Akurat, cepat, konsisten |
| 5 | Update stok otomatis | Data selalu up-to-date |
| 6 | Posting jurnal otomatis | Laporan keuangan standar akuntansi |
| 7 | Laporan real-time | Bisa diakses kapan saja |
| 8 | Metode FIFO | Perhitungan biaya lebih akurat |
| 9 | Audit trail | Semua transaksi tercatat |
| 10 | Multi-user | Bisa diakses bersamaan |

---

## 3. BPMN Proses Pembelian Bahan Baku

### Sistem Saat Ini (As-Is)

```mermaid
graph LR
    A[Vendor Kirim<br/>Bahan] --> B[Admin Terima<br/>& Cek Fisik]
    B --> C[Catat di Buku<br/>Pembelian]
    C --> D[Hitung Total<br/>Manual]
    D --> E[Catat Utang<br/>di Buku Terpisah]
    E --> F[Update Stok<br/>Manual]
    F --> G[Arsip Nota]
    
    style A fill:#FFE4B5
    style B fill:#FFB6C1
    style C fill:#FFB6C1
    style D fill:#FFB6C1
    style E fill:#FFB6C1
    style F fill:#FFB6C1
    style G fill:#D3D3D3
```

### Sistem Usulan (To-Be)

```mermaid
graph LR
    A[Vendor Kirim<br/>Bahan] --> B[Admin Input<br/>di Sistem]
    B --> C[Sistem Hitung<br/>Total Otomatis]
    C --> D[Sistem Catat<br/>Utang Otomatis]
    D --> E[Sistem Update<br/>Stok & Layer]
    E --> F[Sistem Posting<br/>Jurnal]
    F --> G[Laporan<br/>Tersedia]
    
    style A fill:#FFE4B5
    style B fill:#87CEEB
    style C fill:#87CEEB
    style D fill:#87CEEB
    style E fill:#87CEEB
    style F fill:#87CEEB
    style G fill:#98FB98
```

---

## 4. BPMN Proses Penjualan Produk

### Sistem Saat Ini (As-Is)

```mermaid
graph LR
    A[Customer<br/>Pesan] --> B[Admin Cek<br/>Stok Manual]
    B --> C{Stok<br/>Ada?}
    C -->|Tidak| D[Tolak Pesanan]
    C -->|Ya| E[Catat Penjualan<br/>di Buku]
    E --> F[Hitung Total<br/>Manual]
    F --> G[Kurangi Stok<br/>Manual]
    G --> H[Catat Kas<br/>di Buku Lain]
    
    style A fill:#FFE4B5
    style B fill:#FFB6C1
    style C fill:#FFD700
    style E fill:#FFB6C1
    style F fill:#FFB6C1
    style G fill:#FFB6C1
    style H fill:#FFB6C1
```

### Sistem Usulan (To-Be)

```mermaid
graph LR
    A[Customer<br/>Pesan] --> B[Admin Input<br/>di Sistem]
    B --> C{Sistem Cek<br/>Stok Real-time}
    C -->|Tidak| D[Sistem Tolak<br/>& Beri Info]
    C -->|Ya| E[Sistem Hitung<br/>Total & Diskon]
    E --> F[Sistem Kurangi<br/>Stok FIFO]
    F --> G[Sistem Posting<br/>Jurnal]
    G --> H[Cetak Invoice]
    
    style A fill:#FFE4B5
    style B fill:#87CEEB
    style C fill:#FFD700
    style E fill:#87CEEB
    style F fill:#87CEEB
    style G fill:#87CEEB
    style H fill:#98FB98
```

---

## 5. BPMN Proses Pelaporan Keuangan

### Sistem Saat Ini (As-Is)

```mermaid
graph TB
    A[Akhir Bulan] --> B[Admin Kumpulkan<br/>Semua Buku Catatan]
    B --> C[Hitung Total<br/>Penjualan Manual]
    C --> D[Hitung Total<br/>Pembelian Manual]
    D --> E[Hitung Total<br/>Beban Manual]
    E --> F[Hitung Laba/Rugi<br/>dengan Kalkulator]
    F --> G[Ketik Laporan<br/>di Excel]
    G --> H[Print & Arsip]
    
    style A fill:#FFE4B5
    style B fill:#FFB6C1
    style C fill:#FFB6C1
    style D fill:#FFB6C1
    style E fill:#FFB6C1
    style F fill:#FFB6C1
    style G fill:#FFB6C1
    style H fill:#D3D3D3
```

### Sistem Usulan (To-Be)

```mermaid
graph TB
    A[Kapan Saja] --> B[Owner/Admin<br/>Login Sistem]
    B --> C[Pilih Menu<br/>Laporan]
    C --> D[Pilih Jenis Laporan<br/>& Periode]
    D --> E[Sistem Generate<br/>Laporan Otomatis]
    E --> F[Tampilkan Laporan<br/>Real-time]
    F --> G{Perlu<br/>Export?}
    G -->|Ya| H[Export PDF/Excel]
    G -->|Tidak| I[Selesai]
    H --> I
    
    style A fill:#FFE4B5
    style B fill:#87CEEB
    style C fill:#87CEEB
    style D fill:#87CEEB
    style E fill:#87CEEB
    style F fill:#98FB98
    style G fill:#FFD700
    style H fill:#98FB98
    style I fill:#90EE90
```

---

## Perbandingan Waktu Proses

| Proses | Sistem Saat Ini | Sistem Usulan | Efisiensi |
|--------|----------------|---------------|-----------|
| Input Produksi | 15-20 menit | 2-3 menit | 85% lebih cepat |
| Cek Stok Bahan | 10-15 menit | Real-time (< 1 detik) | 99% lebih cepat |
| Hitung HPP | 20-30 menit | Otomatis (< 1 detik) | 99% lebih cepat |
| Update Stok | 10 menit | Otomatis | 100% lebih cepat |
| Buat Laporan Bulanan | 4-6 jam | 1-2 menit | 99% lebih cepat |
| Cari Data Lama | 30-60 menit | < 10 detik | 99% lebih cepat |

---

## Kesimpulan

Sistem UMKM COE memberikan peningkatan signifikan dalam:
1. ✅ **Efisiensi Waktu** - Proses 85-99% lebih cepat
2. ✅ **Akurasi Data** - Validasi otomatis, perhitungan konsisten
3. ✅ **Integrasi Data** - Semua data terhubung dalam satu sistem
4. ✅ **Real-time Information** - Data selalu up-to-date
5. ✅ **Standar Akuntansi** - Jurnal dan laporan sesuai standar
6. ✅ **Kemudahan Akses** - Bisa diakses kapan saja, dimana saja
7. ✅ **Audit Trail** - Semua transaksi tercatat lengkap
8. ✅ **Skalabilitas** - Mudah dikembangkan sesuai kebutuhan
