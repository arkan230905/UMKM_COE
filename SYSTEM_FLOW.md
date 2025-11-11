# DOKUMENTASI ALUR SISTEM UMKM COE EADT

## DAFTAR ISI
1. [Master Data](#master-data)
2. [Transaksi](#transaksi)
3. [Laporan](#laporan)
4. [Akuntansi](#akuntansi)

---

## MASTER DATA

### 1. PEGAWAI
**Alur:**
- Index: Menampilkan daftar pegawai dengan filter dan search
- Create: Form input data pegawai (nama, NIK, jabatan, gaji pokok, tunjangan, bank, dll)
- Store: Validasi → Generate kode pegawai otomatis → Simpan ke database
- Edit: Load data pegawai → Form edit
- Update: Validasi → Update data → Redirect ke index
- Delete: Cek apakah pegawai punya transaksi → Hapus jika tidak ada

**Validasi:**
- Nama wajib diisi
- NIK unik
- Jabatan harus ada di master jabatan
- Gaji pokok dan tunjangan harus angka positif
- Bank dan nomor rekening wajib untuk penggajian

**Relasi:**
- Belongs to Jabatan
- Has many Presensi
- Has many Penggajian

---

### 2. PRESENSI
**Alur:**
- Index: Menampilkan daftar presensi dengan filter tanggal dan pegawai
- Create: Pilih pegawai → Input tanggal, jam masuk, jam keluar, keterangan
- Store: Validasi → Hitung jumlah jam kerja → Simpan
- Edit: Load data presensi → Form edit
- Update: Validasi → Update data
- Delete: Hapus data presensi

**Validasi:**
- Pegawai wajib dipilih
- Tanggal tidak boleh di masa depan
- Jam keluar harus lebih besar dari jam masuk
- Tidak boleh duplikat (pegawai + tanggal)

**Perhitungan:**
- Jumlah jam kerja = Jam keluar - Jam masuk
- Overtime = Jam kerja > 8 jam

---

### 3. PRODUK
**Alur:**
- Index: Menampilkan daftar produk dengan stok dan harga
- Create: Input nama, kategori, satuan, harga jual, margin
- Store: Validasi → Generate kode produk → Simpan
- Edit: Load data produk → Form edit
- Update: Validasi → Update data → Update BOM jika ada
- Delete: Cek stok dan transaksi → Hapus jika memungkinkan

**Validasi:**
- Nama produk wajib dan unik
- Satuan harus ada di master satuan
- Harga jual harus positif
- Margin dalam persen (0-100)

**Perhitungan Harga Pokok:**
- Harga Pokok = Total Biaya Bahan Baku + BTKL + BOP
- Harga Jual = Harga Pokok + (Harga Pokok × Margin%)

---

### 4. VENDOR
**Alur:**
- Index: Daftar vendor dengan kategori (supplier/customer)
- Create: Input nama, kategori, alamat, telepon, email
- Store: Validasi → Simpan
- Edit: Load data → Form edit
- Update: Validasi → Update
- Delete: Cek transaksi → Hapus jika tidak ada transaksi

**Validasi:**
- Nama vendor wajib
- Kategori: supplier atau customer
- Telepon dan email format valid
- Email unik

---

### 5. BAHAN BAKU
**Alur:**
- Index: Daftar bahan baku dengan stok dan harga
- Create: Input nama, satuan, harga beli, stok awal
- Store: Validasi → Generate kode → Simpan → Buat stock layer
- Edit: Load data → Form edit
- Update: Validasi → Update data
- Delete: Cek penggunaan di BOM dan stok → Hapus jika memungkinkan

**Validasi:**
- Nama wajib dan unik
- Satuan harus ada di master satuan
- Harga beli positif
- Stok tidak boleh negatif

**Manajemen Stok:**
- Metode: FIFO (First In First Out)
- Stock Layer: Mencatat setiap pembelian dengan harga dan qty
- Stock Movement: Mencatat setiap pergerakan stok (in/out)

---

### 6. SATUAN
**Alur:**
- Index: Daftar satuan dengan faktor konversi
- Create: Input nama, singkatan, faktor konversi
- Store: Validasi → Simpan
- Edit: Load data → Form edit
- Update: Validasi → Update
- Delete: Cek penggunaan → Hapus jika tidak digunakan

**Validasi:**
- Nama dan singkatan wajib
- Faktor konversi positif (default 1)

**Konversi:**
- Faktor = 1 untuk satuan dasar
- Contoh: 1 Kg = 1000 gram (faktor = 1000)

---

### 7. COA (Chart of Accounts)
**Alur:**
- Index: Daftar akun dengan hierarki
- Create: Input kode, nama, tipe, parent (opsional)
- Store: Validasi → Generate kode otomatis → Simpan
- Edit: Load data → Form edit
- Update: Validasi → Update
- Delete: Cek transaksi → Hapus jika tidak ada jurnal

**Validasi:**
- Kode akun unik
- Nama wajib
- Tipe: Aset, Liabilitas, Ekuitas, Pendapatan, Beban
- Parent harus ada jika diisi

**Struktur Kode:**
- 1xxx: Aset
- 2xxx: Liabilitas
- 3xxx: Ekuitas
- 4xxx: Pendapatan
- 5xxx: Beban

---

### 8. BOM (Bill of Materials)
**Alur:**
- Index: Daftar BOM per produk
- Create: Pilih produk → Input bahan baku, qty, harga
- Store: Validasi → Hitung total biaya → Simpan → Update harga pokok produk
- Edit: Load data → Form edit
- Update: Validasi → Hitung ulang → Update
- Delete: Hapus BOM → Recalculate harga pokok produk

**Validasi:**
- Produk wajib dipilih
- Minimal 1 bahan baku
- Qty harus positif
- Bahan baku tidak boleh duplikat

**Perhitungan:**
- Total Biaya Bahan = Σ (Qty × Harga Bahan)
- BTKL = Input manual atau dari alokasi pegawai
- BOP = Input manual atau dari budget BOP
- Total Biaya = Bahan + BTKL + BOP

---

### 9. ASET
**Alur:**
- Index: Daftar aset dengan nilai buku dan status
- Create: Input nama, jenis, kategori, harga perolehan, biaya perolehan, metode penyusutan, umur manfaat, nilai residu
- Store: Validasi → Generate kode aset → Hitung penyusutan → Simpan
- Edit: Load data → Form edit
- Update: Validasi → Recalculate penyusutan → Update
- Delete: Cek akumulasi penyusutan → Hapus jika belum ada penyusutan

**Validasi:**
- Nama aset wajib
- Jenis dan kategori harus ada
- Harga perolehan dan biaya perolehan positif
- Umur manfaat 1-100 tahun
- Nilai residu ≤ Total perolehan

**Perhitungan Penyusutan:**

1. **Garis Lurus (Straight Line):**
   ```
   Penyusutan/Tahun = (Total Perolehan - Nilai Residu) / Umur Manfaat
   Penyusutan/Bulan = Penyusutan/Tahun / 12
   ```

2. **Saldo Menurun (Declining Balance):**
   ```
   Rate = 2 / Umur Manfaat
   Penyusutan/Tahun = Nilai Buku × Rate
   Penyusutan/Bulan = Penyusutan/Tahun / 12
   ```

3. **Jumlah Angka Tahun (Sum of Years Digits):**
   ```
   Sum of Years = (n × (n + 1)) / 2
   Penyusutan Tahun ke-1 = (Nilai Terdepresiasi × n) / Sum of Years
   Penyusutan/Bulan = Penyusutan/Tahun / 12
   ```

**Keterangan:**
- Total Perolehan = Harga Perolehan + Biaya Perolehan
- Nilai Terdepresiasi = Total Perolehan - Nilai Residu
- Nilai Buku = Total Perolehan - Akumulasi Penyusutan

---

## TRANSAKSI

### 1. PEMBELIAN
**Alur:**
- Index: Daftar pembelian dengan status pembayaran
- Create: Pilih vendor → Pilih bahan baku → Input qty, harga → Pilih metode pembayaran
- Store: Validasi → Generate nomor pembelian → Simpan pembelian → Simpan detail → Update stok (FIFO) → Buat jurnal → Update utang jika kredit
- Edit: Load data → Form edit (hanya jika belum lunas)
- Update: Validasi → Update data → Adjust stok → Update jurnal
- Delete: Cek status → Reverse stok → Reverse jurnal → Hapus

**Validasi:**
- Vendor wajib dipilih
- Minimal 1 item
- Qty dan harga positif
- Metode pembayaran: tunai/kredit
- Tanggal tidak boleh di masa depan

**Jurnal Pembelian Tunai:**
```
Dr. Persediaan Bahan Baku    xxx
    Cr. Kas                      xxx
```

**Jurnal Pembelian Kredit:**
```
Dr. Persediaan Bahan Baku    xxx
    Cr. Utang Usaha              xxx
```

**Manajemen Stok:**
- Buat Stock Layer baru untuk setiap pembelian
- Stock Movement: IN dengan referensi nomor pembelian
- Update stok bahan baku

---

### 2. PENJUALAN
**Alur:**
- Index: Daftar penjualan dengan status pembayaran
- Create: Pilih customer → Pilih produk → Input qty, harga → Diskon → Pilih metode pembayaran
- Store: Validasi → Generate nomor penjualan → Cek stok → Simpan penjualan → Simpan detail → Kurangi stok (FIFO) → Hitung HPP → Buat jurnal
- Edit: Load data → Form edit (hanya jika belum lunas)
- Update: Validasi → Update data → Adjust stok → Update jurnal
- Delete: Cek status → Reverse stok → Reverse jurnal → Hapus

**Validasi:**
- Customer wajib dipilih
- Minimal 1 item
- Qty harus ≤ stok tersedia
- Harga positif
- Diskon 0-100%

**Jurnal Penjualan Tunai:**
```
Dr. Kas                      xxx
    Cr. Penjualan                xxx

Dr. HPP                      xxx
    Cr. Persediaan Produk        xxx
```

**Jurnal Penjualan Kredit:**
```
Dr. Piutang Usaha            xxx
    Cr. Penjualan                xxx

Dr. HPP                      xxx
    Cr. Persediaan Produk        xxx
```

**Perhitungan:**
- Subtotal = Σ (Qty × Harga)
- Diskon = Subtotal × Diskon%
- Total = Subtotal - Diskon
- HPP = Diambil dari stock layer (FIFO)

---

### 3. RETUR
**Alur:**
- Index: Daftar retur dengan status approval
- Create: Pilih transaksi (pembelian/penjualan) → Pilih item → Input qty retur, alasan
- Store: Validasi → Generate nomor retur → Simpan → Status: pending
- Approve: Validasi → Update stok → Buat jurnal → Status: approved
- Post: Posting ke akuntansi → Status: posted
- Delete: Hapus jika masih pending

**Validasi:**
- Transaksi referensi wajib
- Qty retur ≤ Qty transaksi
- Alasan wajib diisi
- Tidak boleh retur yang sudah diretur

**Retur Pembelian (Return to Supplier):**
```
Dr. Utang Usaha / Kas        xxx
    Cr. Persediaan Bahan Baku    xxx
```

**Retur Penjualan (Return from Customer):**
```
Dr. Retur Penjualan          xxx
    Cr. Piutang / Kas            xxx

Dr. Persediaan Produk        xxx
    Cr. HPP                      xxx
```

---

### 4. PRODUKSI
**Alur:**
- Index: Daftar produksi dengan status
- Create: Pilih produk → Input qty produksi → Load BOM → Cek stok bahan
- Store: Validasi → Generate nomor produksi → Kurangi stok bahan (FIFO) → Tambah stok produk → Alokasi BTKL dan BOP → Buat jurnal
- Show: Detail produksi dengan breakdown biaya
- Delete: Reverse stok → Reverse jurnal → Hapus

**Validasi:**
- Produk wajib dipilih
- Qty produksi positif
- Stok bahan baku harus cukup
- BOM harus sudah dibuat

**Perhitungan Biaya Produksi:**
```
Biaya Bahan Baku = Σ (Qty Bahan × Harga FIFO)
BTKL = Jam Kerja × Tarif per Jam
BOP = Alokasi BOP berdasarkan driver
Total Biaya = Bahan + BTKL + BOP
HPP per Unit = Total Biaya / Qty Produksi
```

**Jurnal Produksi:**
```
Dr. Barang Dalam Proses      xxx
    Cr. Persediaan Bahan Baku    xxx

Dr. Barang Dalam Proses      xxx
    Cr. Gaji & Upah              xxx

Dr. Barang Dalam Proses      xxx
    Cr. BOP                      xxx

Dr. Persediaan Produk Jadi   xxx
    Cr. Barang Dalam Proses      xxx
```

---

### 5. PENGGAJIAN
**Alur:**
- Index: Daftar penggajian per periode
- Create: Pilih periode → Load pegawai → Hitung gaji berdasarkan presensi
- Store: Validasi → Hitung total gaji → Simpan → Buat jurnal
- Show: Detail slip gaji per pegawai
- Print: Cetak slip gaji
- Delete: Reverse jurnal → Hapus

**Validasi:**
- Periode wajib dipilih
- Minimal 1 pegawai
- Tidak boleh duplikat periode + pegawai

**Perhitungan Gaji:**
```
Gaji Pokok = Dari master pegawai
Tunjangan = Dari master pegawai
Lembur = Jam Lembur × Tarif Lembur
Potongan = BPJS, Pinjaman, dll
Total Gaji = Gaji Pokok + Tunjangan + Lembur - Potongan
```

**Jurnal Penggajian:**
```
Dr. Beban Gaji               xxx
    Cr. Utang Gaji               xxx

Dr. Utang Gaji               xxx
    Cr. Kas                      xxx
```

---

### 6. PEMBAYARAN BEBAN
**Alur:**
- Index: Daftar pembayaran beban
- Create: Pilih akun beban → Input nominal, tanggal, keterangan
- Store: Validasi → Generate nomor → Simpan → Buat jurnal
- Show: Detail pembayaran
- Print: Cetak bukti pembayaran
- Delete: Reverse jurnal → Hapus

**Validasi:**
- Akun beban wajib dipilih
- Nominal positif
- Tanggal tidak boleh di masa depan
- Keterangan wajib

**Jurnal Pembayaran Beban:**
```
Dr. Beban [Nama Akun]        xxx
    Cr. Kas                      xxx
```

---

### 7. PELUNASAN UTANG
**Alur:**
- Index: Daftar pelunasan dengan sisa utang
- Create: Pilih pembelian yang belum lunas → Input nominal bayar
- Store: Validasi → Generate nomor → Simpan → Update sisa utang → Buat jurnal
- Show: Detail pelunasan
- Print: Cetak bukti pelunasan
- Delete: Reverse pembayaran → Update sisa utang → Reverse jurnal → Hapus

**Validasi:**
- Pembelian wajib dipilih
- Nominal ≤ Sisa utang
- Tanggal ≥ Tanggal pembelian

**Jurnal Pelunasan:**
```
Dr. Utang Usaha              xxx
    Cr. Kas                      xxx
```

---

## LAPORAN

### 1. LAPORAN STOK
**Fitur:**
- Filter: Kategori (Bahan Baku/Produk), Tanggal
- Kolom: Kode, Nama, Satuan, Stok Awal, Masuk, Keluar, Stok Akhir, Nilai Stok
- Export: Excel, PDF

**Perhitungan:**
```
Stok Akhir = Stok Awal + Masuk - Keluar
Nilai Stok = Stok Akhir × Harga Rata-rata
```

---

### 2. LAPORAN PEMBELIAN
**Fitur:**
- Filter: Vendor, Tanggal Mulai, Tanggal Akhir, Status Pembayaran
- Kolom: No Pembelian, Tanggal, Vendor, Total, Dibayar, Sisa, Status
- Detail: Item pembelian dengan qty dan harga
- Export: Excel, PDF
- Print Invoice

**Summary:**
- Total Pembelian
- Total Dibayar
- Total Utang

---

### 3. LAPORAN PENJUALAN
**Fitur:**
- Filter: Customer, Tanggal Mulai, Tanggal Akhir, Status Pembayaran
- Kolom: No Penjualan, Tanggal, Customer, Subtotal, Diskon, Total, Status
- Detail: Item penjualan dengan qty, harga, HPP
- Export: Excel, PDF
- Print Invoice

**Summary:**
- Total Penjualan
- Total HPP
- Laba Kotor
- Margin %

---

### 4. LAPORAN RETUR
**Fitur:**
- Filter: Tipe (Pembelian/Penjualan), Tanggal, Status
- Kolom: No Retur, Tanggal, Referensi, Tipe, Total, Status
- Detail: Item retur dengan qty dan alasan
- Export: Excel, PDF

---

### 5. LAPORAN PENGGAJIAN
**Fitur:**
- Filter: Periode, Pegawai
- Kolom: Nama Pegawai, Gaji Pokok, Tunjangan, Lembur, Potongan, Total
- Detail: Breakdown per komponen gaji
- Export: Excel, PDF
- Print Slip Gaji

**Summary:**
- Total Gaji Pokok
- Total Tunjangan
- Total Lembur
- Total Potongan
- Total Dibayar

---

### 6. LAPORAN ALIRAN KAS
**Fitur:**
- Filter: Tanggal Mulai, Tanggal Akhir
- Kategori: Kas Masuk, Kas Keluar
- Kolom: Tanggal, Keterangan, Debit, Kredit, Saldo

**Perhitungan:**
```
Saldo Awal = Saldo akhir periode sebelumnya
Kas Masuk = Penjualan Tunai + Pelunasan Piutang + Lainnya
Kas Keluar = Pembelian Tunai + Pembayaran Utang + Beban + Gaji
Saldo Akhir = Saldo Awal + Kas Masuk - Kas Keluar
```

---

### 7. LAPORAN PENYUSUTAN ASET
**Fitur:**
- Filter: Jenis Aset, Kategori, Tahun
- Kolom: Kode Aset, Nama, Harga Perolehan, Akumulasi Penyusutan, Nilai Buku
- Detail: Jadwal penyusutan per bulan/tahun
- Posting: Posting penyusutan bulanan ke jurnal

**Jurnal Penyusutan:**
```
Dr. Beban Penyusutan         xxx
    Cr. Akumulasi Penyusutan     xxx
```

---

## AKUNTANSI

### 1. JURNAL UMUM
**Fitur:**
- Filter: Tanggal, Akun, Referensi
- Kolom: Tanggal, No Jurnal, Akun, Debit, Kredit, Keterangan
- Auto-generated dari transaksi
- Manual entry untuk jurnal penyesuaian

**Validasi:**
- Total Debit = Total Kredit
- Minimal 2 baris (1 debit, 1 kredit)

---

### 2. BUKU BESAR
**Fitur:**
- Filter: Akun, Periode
- Kolom: Tanggal, Keterangan, Ref, Debit, Kredit, Saldo
- Saldo Running per transaksi

**Perhitungan:**
```
Saldo = Saldo Sebelumnya + Debit - Kredit (untuk akun Debit normal)
Saldo = Saldo Sebelumnya - Debit + Kredit (untuk akun Kredit normal)
```

---

### 3. NERACA SALDO
**Fitur:**
- Filter: Periode
- Kolom: Kode Akun, Nama Akun, Debit, Kredit
- Total harus balance

**Validasi:**
```
Total Debit = Total Kredit
```

---

### 4. LABA RUGI
**Fitur:**
- Filter: Periode
- Struktur:
  - Pendapatan
  - HPP
  - Laba Kotor
  - Beban Operasional
  - Laba Operasional
  - Pendapatan/Beban Lain
  - Laba Bersih

**Perhitungan:**
```
Laba Kotor = Pendapatan - HPP
Laba Operasional = Laba Kotor - Beban Operasional
Laba Bersih = Laba Operasional + Pendapatan Lain - Beban Lain
```

---

## CATATAN PENTING

### Manajemen Stok (FIFO)
1. Setiap pembelian membuat Stock Layer baru
2. Setiap penjualan/produksi mengambil dari Stock Layer tertua
3. Stock Movement mencatat semua pergerakan
4. HPP dihitung dari harga di Stock Layer yang digunakan

### Integrasi Akuntansi
1. Semua transaksi otomatis membuat jurnal
2. Jurnal tidak bisa diedit manual (kecuali jurnal penyesuaian)
3. Hapus transaksi akan reverse jurnal
4. Posting ke buku besar dilakukan real-time

### Validasi Bisnis
1. Tidak boleh edit/hapus transaksi yang sudah di-posting
2. Tidak boleh hapus master data yang sudah digunakan
3. Stok tidak boleh negatif
4. Tanggal transaksi tidak boleh di masa depan
5. Total debit harus sama dengan total kredit

### Keamanan
1. Semua route dilindungi middleware auth
2. Soft delete untuk data penting
3. Audit trail untuk perubahan data
4. Backup database otomatis

---

**Versi:** 1.0  
**Terakhir Diupdate:** 10 November 2025  
**Dibuat oleh:** Tim Development COE EADT
