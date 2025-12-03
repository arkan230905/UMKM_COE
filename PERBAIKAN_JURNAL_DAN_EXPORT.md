# Perbaikan Jurnal Umum dan Export Laporan

## Masalah yang Diperbaiki

### 1. Jurnal Umum - Keterangan Akun Tidak Muncul
**Masalah**: Akun 121 dan akun lainnya tidak menampilkan nama/keterangan akun

**Solusi**:
- Memperbaiki tampilan view jurnal umum dengan memisahkan kolom "Kode Akun" dan "Nama Akun"
- Menambahkan informasi tipe akun sebagai keterangan tambahan
- Menambahkan badge untuk membedakan Debit (D) dan Kredit (K)
- Menambahkan total di footer tabel

**File yang Diubah**:
- `resources/views/akuntansi/jurnal-umum.blade.php`

### 2. Export PDF dan Excel untuk Jurnal Umum
**Fitur Baru**: Menambahkan tombol download PDF dan Excel di jurnal umum

**Implementasi**:
- Membuat view PDF: `resources/views/akuntansi/jurnal-umum-pdf.blade.php`
- Membuat class Export Excel: `app/Exports/JurnalUmumExport.php`
- Menambahkan method di controller:
  - `jurnalUmumExportPdf()` - Export ke PDF
  - `jurnalUmumExportExcel()` - Export ke Excel
- Menambahkan route:
  - `akuntansi.jurnal-umum.export-pdf`
  - `akuntansi.jurnal-umum.export-excel`

**Fitur Export Excel**:
- Header dengan styling (background biru, teks putih)
- Auto-size kolom
- Format currency untuk kolom Debit dan Kredit
- Total otomatis di baris terakhir
- Border pada semua cell

**Fitur Export PDF**:
- Layout landscape A4
- Header dengan informasi periode
- Styling yang rapi dengan border
- Badge untuk Debit/Kredit
- Total di footer

## Cara Menggunakan

### Jurnal Umum
1. Buka menu **Akuntansi > Jurnal Umum**
2. Filter berdasarkan:
   - Tanggal (Dari - Sampai)
   - Ref Type (opsional)
   - Ref ID (opsional)
3. Klik **Download PDF** atau **Download Excel** untuk export

### Data yang Ditampilkan - Jurnal Umum
- **Tanggal**: Tanggal transaksi (format dd/mm/yyyy)
- **Ref**: Referensi transaksi (ref_type#ref_id)
- **Memo**: Keterangan transaksi
- **Kode Akun**: Kode akun (misal: 121)
- **Nama Akun**: Nama lengkap akun (misal: Piutang Usaha)
- **Tipe Akun**: Tipe akun (asset, liability, equity, revenue, expense)
- **Debit**: Jumlah debit (jika ada)
- **Kredit**: Jumlah kredit (jika ada)
- **Total**: Total debit dan kredit di footer

### Laporan Kas dan Bank
1. Buka menu **Laporan > Kas dan Bank**
2. Filter berdasarkan periode:
   - Tanggal Mulai
   - Tanggal Akhir
   - Quick filter: Hari Ini, Minggu Ini, Bulan Ini, Tahun Ini
3. Klik **Download PDF** atau **Download Excel** untuk export

### Data yang Ditampilkan - Laporan Kas dan Bank
- **Kode Akun**: Kode akun kas/bank (101, 102, dll)
- **Nama Akun**: Nama akun (Kas, Bank BCA, dll)
- **Saldo Awal**: Saldo awal periode
- **Transaksi Masuk**: Total debit dalam periode
- **Transaksi Keluar**: Total kredit dalam periode
- **Saldo Akhir**: Saldo akhir periode (Saldo Awal + Masuk - Keluar)
- **Total**: Total saldo akhir semua akun kas dan bank

## Laporan Lain yang Sudah Memiliki Export

### ✅ Sudah Ada Export PDF dan Excel:
1. **Jurnal Umum** - PDF & Excel (BARU)
2. **Laporan Kas dan Bank** - PDF & Excel (BARU)

### Sudah Ada Export PDF:
1. Laporan Retur
2. Laporan Penggajian
3. Laporan Pembayaran Beban
4. Laporan Pelunasan Utang

### Sudah Ada Export Excel (CSV):
1. Laporan Pembelian

### Belum Ada Export (Perlu Ditambahkan):
1. Laporan Stok
2. Laporan Aliran Kas
3. Laporan Penjualan
4. Buku Besar
5. Neraca Saldo
6. Laba Rugi

## Catatan Teknis

### Dependencies yang Digunakan:
- `barryvdh/laravel-dompdf` - untuk PDF
- `maatwebsite/laravel-excel` - untuk Excel

### Format Export:
- **PDF**: Landscape A4, font Arial 10px
- **Excel**: XLSX dengan styling dan formula

### Validasi Data:
- Semua relasi account sudah di-load dengan eager loading
- Fallback untuk data yang tidak ada (misal: "Akun tidak ditemukan")
- Format currency Indonesia (Rp dengan pemisah ribuan)

## Testing

### Test Jurnal Umum:
1. Buka jurnal umum tanpa filter - harus menampilkan semua data
2. Filter berdasarkan tanggal - harus menampilkan data sesuai periode
3. Filter berdasarkan ref_type - harus menampilkan data sesuai tipe
4. Cek kolom "Nama Akun" - harus menampilkan nama lengkap akun (misal: akun 121 = "Piutang Usaha")
5. Download PDF - harus ter-download dengan data lengkap
6. Download Excel - harus ter-download dengan data lengkap dan formula total

### Test Laporan Kas dan Bank:
1. Buka laporan kas dan bank
2. Filter berdasarkan periode (tanggal mulai - tanggal akhir)
3. Cek data saldo awal, transaksi masuk, transaksi keluar, dan saldo akhir
4. Download PDF - harus ter-download dengan data lengkap
5. Download Excel - harus ter-download dengan data lengkap dan formula total

### Verifikasi Data:
- **Jurnal Umum**: Total Debit = Total Kredit (harus balance)
- **Laporan Kas Bank**: Saldo Akhir = Saldo Awal + Transaksi Masuk - Transaksi Keluar
- Semua akun memiliki kode dan nama lengkap
- Format tanggal konsisten (dd/mm/yyyy)
- Format currency konsisten (Rp dengan pemisah ribuan)
- Export PDF dan Excel memiliki data yang sama dengan tampilan web
