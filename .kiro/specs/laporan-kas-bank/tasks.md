# Implementation Plan - Laporan Kas & Bank Real-Time

- [ ] 1. Setup Controller dan Routes
  - [ ] 1.1 Buat LaporanKasBankController dengan method index
    - Buat controller di `app/Http/Controllers/LaporanKasBankController.php`
    - Implement method index untuk tampilkan halaman laporan
    - _Requirements: 1.1_
  
  - [ ] 1.2 Tambahkan routes untuk laporan kas & bank
    - Tambahkan route GET untuk index
    - Tambahkan route POST untuk getData (AJAX)
    - Tambahkan route GET untuk export
    - _Requirements: 1.1_

- [ ] 2. Implement Perhitungan Saldo
  - [ ] 2.1 Buat method getSaldoAwal di controller
    - Query saldo awal dari COA atau jurnal sebelum periode
    - Handle jika belum ada saldo awal
    - _Requirements: 1.3, 4.3_
  
  - [ ] 2.2 Buat method getTransaksiMasuk di controller
    - Query transaksi penjualan yang sudah lunas
    - Filter berdasarkan akun kas/bank dan periode
    - Hitung total transaksi masuk
    - _Requirements: 2.1, 2.2, 2.3_
  
  - [ ] 2.3 Buat method getTransaksiKeluar di controller
    - Query transaksi pembelian
    - Query pembayaran beban
    - Query pelunasan utang
    - Query penggajian
    - Query retur penjualan
    - Filter berdasarkan akun kas/bank dan periode
    - Hitung total transaksi keluar
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8_
  
  - [ ] 2.4 Buat method getSaldoAkhir di controller
    - Hitung saldo akhir = saldo awal + masuk - keluar
    - Validasi hasil perhitungan
    - _Requirements: 1.3_

- [ ] 3. Buat View Laporan Kas & Bank
  - [ ] 3.1 Buat file view index.blade.php
    - Buat layout halaman dengan header
    - Tambahkan section untuk total kas & bank
    - Tambahkan filter periode
    - _Requirements: 1.1, 4.1, 4.2_
  
  - [ ] 3.2 Buat tabel saldo per akun kas/bank
    - Tampilkan kolom: Akun, Saldo Awal, Masuk, Keluar, Saldo Akhir
    - Tambahkan tombol detail untuk transaksi masuk dan keluar
    - Styling dengan Bootstrap
    - _Requirements: 1.2, 1.3_
  
  - [ ] 3.3 Implement filter periode
    - Tambahkan input tanggal mulai dan akhir
    - Tambahkan quick filter (Hari Ini, Minggu Ini, Bulan Ini, Tahun Ini)
    - Implement AJAX untuk update data saat filter berubah
    - _Requirements: 4.1, 4.2, 4.4_

- [ ] 4. Implement Modal Detail Transaksi
  - [ ] 4.1 Buat modal untuk detail transaksi masuk
    - Tampilkan tabel transaksi penjualan
    - Kolom: Tanggal, No Transaksi, Keterangan, Nominal
    - Tambahkan pagination
    - _Requirements: 2.1, 2.2_
  
  - [ ] 4.2 Buat modal untuk detail transaksi keluar
    - Tampilkan tabel semua jenis transaksi keluar
    - Kolom: Tanggal, Jenis, No Transaksi, Keterangan, Nominal
    - Tambahkan filter jenis transaksi
    - Tambahkan pagination
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

- [ ] 5. Implement Export dan Print
  - [ ] 5.1 Buat method export ke Excel
    - Install/gunakan library PhpSpreadsheet atau Laravel Excel
    - Format data untuk export
    - Generate file Excel dengan styling
    - _Requirements: 5.1, 5.4, 5.5_
  
  - [ ] 5.2 Buat method export ke PDF
    - Install/gunakan library DomPDF atau TCPDF
    - Format data untuk PDF
    - Generate file PDF dengan styling
    - _Requirements: 5.2, 5.4, 5.5_
  
  - [ ] 5.3 Implement fungsi print
    - Buat CSS khusus untuk print
    - Implement JavaScript untuk trigger print
    - _Requirements: 5.3_

- [ ] 6. Integrasi dengan Modul Transaksi
  - [ ] 6.1 Update modul penjualan untuk update saldo kas/bank
    - Tambahkan logic update saldo saat penjualan lunas
    - Catat di jurnal umum
    - _Requirements: 6.1, 6.7_
  
  - [ ] 6.2 Update modul pembelian untuk update saldo kas/bank
    - Tambahkan logic update saldo saat pembelian dibayar
    - Catat di jurnal umum
    - _Requirements: 6.2, 6.7_
  
  - [ ] 6.3 Update modul beban untuk update saldo kas/bank
    - Tambahkan logic update saldo saat pembayaran beban
    - Catat di jurnal umum
    - _Requirements: 6.3, 6.7_
  
  - [ ] 6.4 Update modul pelunasan utang untuk update saldo kas/bank
    - Tambahkan logic update saldo saat pelunasan
    - Catat di jurnal umum
    - _Requirements: 6.4, 6.7_
  
  - [ ] 6.5 Update modul penggajian untuk update saldo kas/bank
    - Tambahkan logic update saldo saat penggajian
    - Catat di jurnal umum
    - _Requirements: 6.5, 6.7_
  
  - [ ] 6.6 Update modul retur untuk update saldo kas/bank
    - Tambahkan logic update saldo saat retur penjualan
    - Catat di jurnal umum
    - _Requirements: 6.6, 6.7_

- [ ] 7. Implement Validasi dan Error Handling
  - [ ] 7.1 Buat validasi saldo mencukupi
    - Check saldo sebelum transaksi keluar
    - Tampilkan peringatan jika saldo tidak cukup
    - _Requirements: 7.1, 7.2_
  
  - [ ] 7.2 Implement konfirmasi untuk saldo negatif
    - Tampilkan modal konfirmasi
    - Log transaksi yang menyebabkan saldo negatif
    - _Requirements: 7.3, 7.4_
  
  - [ ] 7.3 Implement notifikasi saldo minimum
    - Set threshold saldo minimum per akun
    - Tampilkan notifikasi jika mendekati minimum
    - _Requirements: 7.5_

- [ ] 8. Testing dan Debugging
  - [ ] 8.1 Test perhitungan saldo dengan data dummy
    - Buat data transaksi test
    - Verifikasi perhitungan saldo awal, masuk, keluar, akhir
    - _Requirements: All_
  
  - [ ] 8.2 Test filter periode
    - Test dengan berbagai rentang tanggal
    - Test quick filter
    - _Requirements: 4.1, 4.2, 4.4_
  
  - [ ] 8.3 Test export dan print
    - Test export Excel
    - Test export PDF
    - Test print
    - _Requirements: 5.1, 5.2, 5.3_
  
  - [ ] 8.4 Test integrasi dengan modul transaksi
    - Buat transaksi penjualan dan cek update saldo
    - Buat transaksi pembelian dan cek update saldo
    - Buat pembayaran beban dan cek update saldo
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_
