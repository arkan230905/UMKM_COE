# Requirements Document - Laporan Kas & Bank Real-Time

## Introduction

Sistem laporan kas dan bank yang menampilkan saldo real-time dari semua akun kas dan bank, termasuk tracking lengkap semua transaksi masuk (penjualan) dan keluar (pembelian, beban, pelunasan utang, penggajian, retur).

## Glossary

- **System**: Aplikasi UMKM COE
- **Kas**: Akun kas tunai perusahaan
- **Bank**: Akun bank perusahaan
- **Saldo Awal**: Saldo kas/bank di awal periode
- **Transaksi Masuk**: Penjualan dan penerimaan lainnya
- **Transaksi Keluar**: Pembelian, beban, pelunasan utang, penggajian, retur
- **Saldo Akhir**: Saldo kas/bank setelah semua transaksi

## Requirements

### Requirement 1: Tampilan Saldo Kas & Bank

**User Story:** Sebagai user, saya ingin melihat saldo semua akun kas dan bank secara real-time, sehingga saya dapat mengetahui posisi keuangan perusahaan.

#### Acceptance Criteria

1. WHEN user membuka halaman laporan kas & bank, THE System SHALL menampilkan daftar semua akun kas dan bank dengan saldo terkini
2. THE System SHALL menampilkan saldo awal, total transaksi masuk, total transaksi keluar, dan saldo akhir untuk setiap akun
3. THE System SHALL menghitung saldo akhir dengan formula: Saldo Akhir = Saldo Awal + Total Masuk - Total Keluar
4. THE System SHALL menampilkan total keseluruhan kas dan bank di bagian atas halaman
5. THE System SHALL memperbarui saldo secara otomatis setiap kali ada transaksi baru

### Requirement 2: Detail Transaksi Masuk

**User Story:** Sebagai user, saya ingin melihat detail semua transaksi masuk (penerimaan), sehingga saya dapat melacak sumber pemasukan.

#### Acceptance Criteria

1. WHEN user mengklik detail transaksi masuk, THE System SHALL menampilkan daftar lengkap transaksi penjualan
2. THE System SHALL menampilkan tanggal, nomor transaksi, keterangan, dan nominal untuk setiap transaksi masuk
3. THE System SHALL menghitung total transaksi masuk per akun kas/bank
4. THE System SHALL menampilkan transaksi masuk yang sudah dibayar (status lunas)
5. THE System SHALL memfilter transaksi berdasarkan akun kas/bank yang dipilih

### Requirement 3: Detail Transaksi Keluar

**User Story:** Sebagai user, saya ingin melihat detail semua transaksi keluar (pengeluaran), sehingga saya dapat melacak penggunaan dana.

#### Acceptance Criteria

1. WHEN user mengklik detail transaksi keluar, THE System SHALL menampilkan daftar lengkap transaksi pengeluaran
2. THE System SHALL mencatat transaksi keluar dari pembelian bahan baku
3. THE System SHALL mencatat transaksi keluar dari pembayaran beban
4. THE System SHALL mencatat transaksi keluar dari pelunasan utang
5. THE System SHALL mencatat transaksi keluar dari penggajian pegawai
6. THE System SHALL mencatat transaksi keluar dari retur penjualan
7. THE System SHALL menampilkan tanggal, jenis transaksi, keterangan, dan nominal untuk setiap transaksi keluar
8. THE System SHALL menghitung total transaksi keluar per akun kas/bank

### Requirement 4: Filter dan Periode

**User Story:** Sebagai user, saya ingin memfilter laporan berdasarkan periode waktu, sehingga saya dapat melihat pergerakan kas di periode tertentu.

#### Acceptance Criteria

1. THE System SHALL menyediakan filter periode dengan tanggal mulai dan tanggal akhir
2. WHEN user memilih periode, THE System SHALL menampilkan transaksi dalam rentang tanggal tersebut
3. THE System SHALL menghitung saldo awal berdasarkan transaksi sebelum periode yang dipilih
4. THE System SHALL menyediakan pilihan periode cepat (Hari Ini, Minggu Ini, Bulan Ini, Tahun Ini)
5. THE System SHALL menyimpan preferensi periode user untuk sesi berikutnya

### Requirement 5: Export dan Print

**User Story:** Sebagai user, saya ingin mengexport dan mencetak laporan kas & bank, sehingga saya dapat menyimpan atau membagikan laporan.

#### Acceptance Criteria

1. THE System SHALL menyediakan tombol export ke Excel
2. THE System SHALL menyediakan tombol export ke PDF
3. THE System SHALL menyediakan tombol print untuk mencetak laporan
4. WHEN user mengexport, THE System SHALL menyertakan semua data yang ditampilkan di layar
5. THE System SHALL memformat laporan export dengan rapi dan mudah dibaca

### Requirement 6: Integrasi dengan Transaksi

**User Story:** Sebagai user, saya ingin saldo kas & bank terupdate otomatis saat ada transaksi, sehingga data selalu akurat dan real-time.

#### Acceptance Criteria

1. WHEN transaksi penjualan dibuat dengan metode pembayaran tunai/transfer, THE System SHALL menambah saldo akun kas/bank terkait
2. WHEN transaksi pembelian dibuat dengan metode pembayaran tunai/transfer, THE System SHALL mengurangi saldo akun kas/bank terkait
3. WHEN pembayaran beban dilakukan, THE System SHALL mengurangi saldo akun kas/bank terkait
4. WHEN pelunasan utang dilakukan, THE System SHALL mengurangi saldo akun kas/bank terkait
5. WHEN penggajian dilakukan, THE System SHALL mengurangi saldo akun kas/bank terkait
6. WHEN retur penjualan dilakukan, THE System SHALL mengurangi saldo akun kas/bank terkait
7. THE System SHALL mencatat semua perubahan saldo dalam jurnal umum

### Requirement 7: Validasi dan Error Handling

**User Story:** Sebagai user, saya ingin sistem mencegah saldo negatif dan memberikan peringatan, sehingga saya tidak melakukan transaksi melebihi saldo yang tersedia.

#### Acceptance Criteria

1. WHEN saldo akun kas/bank tidak mencukupi untuk transaksi, THE System SHALL menampilkan peringatan
2. THE System SHALL menampilkan saldo tersedia sebelum user melakukan transaksi keluar
3. IF saldo akan menjadi negatif, THEN THE System SHALL meminta konfirmasi user
4. THE System SHALL mencatat transaksi yang menyebabkan saldo negatif untuk audit
5. THE System SHALL menampilkan notifikasi jika saldo kas/bank mendekati batas minimum
