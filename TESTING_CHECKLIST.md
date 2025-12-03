# TESTING CHECKLIST - SISTEM UMKM COE EADT

## MASTER DATA

### ✅ PEGAWAI
- [ ] **Index**: Tampil daftar pegawai dengan pagination
- [ ] **Create**: Form input lengkap (nama, NIK, jabatan, gaji, bank)
- [ ] **Store**: Validasi input, generate kode otomatis, simpan berhasil
- [ ] **Show**: Detail pegawai tampil lengkap
- [ ] **Edit**: Form edit terisi data lama
- [ ] **Update**: Update berhasil, redirect ke index
- [ ] **Delete**: Hapus berhasil jika tidak ada transaksi terkait
- [ ] **Search**: Pencarian by nama/NIK berfungsi
- [ ] **Filter**: Filter by jabatan berfungsi

### ✅ PRESENSI
- [ ] **Index**: Tampil daftar presensi dengan filter tanggal
- [ ] **Create**: Form input presensi (pegawai, tanggal, jam)
- [ ] **Store**: Validasi, hitung jam kerja otomatis, simpan
- [ ] **Edit**: Form edit terisi data lama
- [ ] **Update**: Update berhasil
- [ ] **Delete**: Hapus berhasil
- [ ] **Validation**: Tidak boleh duplikat pegawai + tanggal
- [ ] **Calculation**: Jam kerja dihitung otomatis

### ✅ PRODUK
- [ ] **Index**: Tampil daftar produk dengan stok dan harga
- [ ] **Create**: Form input produk lengkap
- [ ] **Store**: Generate kode, simpan berhasil
- [ ] **Show**: Detail produk dengan BOM
- [ ] **Edit**: Form edit terisi data lama
- [ ] **Update**: Update berhasil, recalculate HPP
- [ ] **Delete**: Hapus berhasil jika stok = 0
- [ ] **Search**: Pencarian berfungsi
- [ ] **HPP Calculation**: HPP dihitung dari BOM

### ✅ VENDOR
- [ ] **Index**: Tampil daftar vendor
- [ ] **Create**: Form input vendor (nama, kategori, kontak)
- [ ] **Store**: Validasi, simpan berhasil
- [ ] **Edit**: Form edit terisi data lama
- [ ] **Update**: Update berhasil
- [ ] **Delete**: Hapus berhasil jika tidak ada transaksi
- [ ] **Filter**: Filter by kategori (supplier/customer)

### ✅ BAHAN BAKU
- [ ] **Index**: Tampil daftar bahan baku dengan stok
- [ ] **Create**: Form input bahan baku
- [ ] **Store**: Generate kode, buat stock layer, simpan
- [ ] **Edit**: Form edit terisi data lama
- [ ] **Update**: Update berhasil
- [ ] **Delete**: Hapus berhasil jika stok = 0
- [ ] **Stock Display**: Stok tampil real-time
- [ ] **Search**: Pencarian berfungsi

### ✅ SATUAN
- [ ] **Index**: Tampil daftar satuan
- [ ] **Create**: Form input satuan dengan faktor konversi
- [ ] **Store**: Validasi, simpan berhasil
- [ ] **Edit**: Form edit terisi data lama
- [ ] **Update**: Update berhasil
- [ ] **Delete**: Hapus berhasil jika tidak digunakan

### ✅ COA (Chart of Accounts)
- [ ] **Index**: Tampil daftar akun dengan hierarki
- [ ] **Create**: Form input COA dengan parent
- [ ] **Store**: Generate kode otomatis, simpan
- [ ] **Edit**: Form edit terisi data lama
- [ ] **Update**: Update berhasil
- [ ] **Delete**: Hapus berhasil jika tidak ada jurnal
- [ ] **Hierarchy**: Tampilan hierarki parent-child
- [ ] **Code Generation**: Kode generate sesuai tipe

### ✅ BOM (Bill of Materials)
- [ ] **Index**: Tampil daftar BOM per produk
- [ ] **Create**: Form input BOM dengan multiple bahan
- [ ] **Store**: Hitung total biaya, update HPP produk, simpan
- [ ] **Show**: Detail BOM dengan breakdown biaya
- [ ] **Edit**: Form edit terisi data lama
- [ ] **Update**: Recalculate biaya, update HPP produk
- [ ] **Delete**: Hapus BOM, recalculate HPP produk
- [ ] **Calculation**: Total biaya dihitung otomatis
- [ ] **Product Update**: HPP produk terupdate otomatis

### ✅ ASET
- [ ] **Index**: Tampil daftar aset dengan nilai buku
- [ ] **Create**: Form input aset lengkap (harga, metode penyusutan, umur manfaat)
- [ ] **Store**: Generate kode, hitung penyusutan, simpan
- [ ] **Show**: Detail aset dengan jadwal penyusutan
- [ ] **Edit**: Form edit terisi data lama
- [ ] **Update**: Recalculate penyusutan, update
- [ ] **Delete**: Hapus berhasil jika akumulasi penyusutan = 0
- [ ] **Depreciation Calculation**: Penyusutan dihitung sesuai metode
- [ ] **Filter**: Filter by jenis, kategori, status

---

## TRANSAKSI

### ✅ PEMBELIAN
- [ ] **Index**: Tampil daftar pembelian dengan status
- [ ] **Create**: Form input pembelian (vendor, items, payment method)
- [ ] **Store**: Generate nomor, simpan, update stok (FIFO), buat jurnal
- [ ] **Show**: Detail pembelian dengan items
- [ ] **Edit**: Form edit terisi data lama (hanya jika belum lunas)
- [ ] **Update**: Update data, adjust stok, update jurnal
- [ ] **Delete**: Reverse stok, reverse jurnal, hapus
- [ ] **Stock Update**: Stok bertambah sesuai qty
- [ ] **Journal Entry**: Jurnal tercatat otomatis
- [ ] **Payment Status**: Status pembayaran terupdate
- [ ] **FIFO**: Stock layer tercatat dengan benar

### ✅ PENJUALAN
- [ ] **Index**: Tampil daftar penjualan dengan status
- [ ] **Create**: Form input penjualan (customer, items, discount)
- [ ] **Store**: Cek stok, generate nomor, simpan, kurangi stok (FIFO), hitung HPP, buat jurnal
- [ ] **Show**: Detail penjualan dengan items dan HPP
- [ ] **Edit**: Form edit terisi data lama (hanya jika belum lunas)
- [ ] **Update**: Update data, adjust stok, update jurnal
- [ ] **Delete**: Reverse stok, reverse jurnal, hapus
- [ ] **Stock Check**: Validasi stok tersedia
- [ ] **Stock Update**: Stok berkurang sesuai qty
- [ ] **HPP Calculation**: HPP dihitung dari FIFO
- [ ] **Journal Entry**: Jurnal penjualan dan HPP tercatat
- [ ] **Discount**: Diskon dihitung dengan benar

### ✅ RETUR
- [ ] **Index**: Tampil daftar retur dengan status
- [ ] **Create**: Form input retur (pilih transaksi, items, alasan)
- [ ] **Store**: Generate nomor, simpan, status pending
- [ ] **Approve**: Update stok, buat jurnal, status approved
- [ ] **Post**: Posting ke akuntansi, status posted
- [ ] **Delete**: Hapus jika masih pending
- [ ] **Stock Update**: Stok terupdate sesuai tipe retur
- [ ] **Journal Entry**: Jurnal retur tercatat
- [ ] **Validation**: Qty retur ≤ Qty transaksi

### ✅ PRODUKSI
- [ ] **Index**: Tampil daftar produksi
- [ ] **Create**: Form input produksi (produk, qty)
- [ ] **Store**: Load BOM, cek stok bahan, kurangi stok bahan (FIFO), tambah stok produk, alokasi BTKL & BOP, buat jurnal
- [ ] **Show**: Detail produksi dengan breakdown biaya
- [ ] **Delete**: Reverse stok, reverse jurnal, hapus
- [ ] **BOM Loading**: BOM terload otomatis
- [ ] **Stock Check**: Validasi stok bahan cukup
- [ ] **Stock Update**: Stok bahan berkurang, stok produk bertambah
- [ ] **Cost Calculation**: Biaya produksi dihitung lengkap (Bahan + BTKL + BOP)
- [ ] **Journal Entry**: Jurnal produksi tercatat

### ✅ PENGGAJIAN
- [ ] **Index**: Tampil daftar penggajian per periode
- [ ] **Create**: Form input penggajian (periode, pegawai)
- [ ] **Store**: Hitung gaji dari presensi, simpan, buat jurnal
- [ ] **Show**: Detail slip gaji per pegawai
- [ ] **Print**: Cetak slip gaji
- [ ] **Delete**: Reverse jurnal, hapus
- [ ] **Salary Calculation**: Gaji dihitung dari gaji pokok + tunjangan + lembur - potongan
- [ ] **Attendance Integration**: Data presensi terintegrasi
- [ ] **Journal Entry**: Jurnal gaji tercatat
- [ ] **Validation**: Tidak boleh duplikat periode + pegawai

### ✅ PEMBAYARAN BEBAN
- [ ] **Index**: Tampil daftar pembayaran beban
- [ ] **Create**: Form input pembayaran (akun beban, nominal, keterangan)
- [ ] **Store**: Generate nomor, simpan, buat jurnal
- [ ] **Show**: Detail pembayaran
- [ ] **Print**: Cetak bukti pembayaran
- [ ] **Delete**: Reverse jurnal, hapus
- [ ] **Journal Entry**: Jurnal beban tercatat
- [ ] **COA Integration**: Akun beban dari master COA

### ✅ PELUNASAN UTANG
- [ ] **Index**: Tampil daftar pelunasan dengan sisa utang
- [ ] **Create**: Form input pelunasan (pilih pembelian, nominal bayar)
- [ ] **Store**: Generate nomor, simpan, update sisa utang, buat jurnal
- [ ] **Show**: Detail pelunasan
- [ ] **Print**: Cetak bukti pelunasan
- [ ] **Delete**: Reverse pembayaran, update sisa utang, reverse jurnal, hapus
- [ ] **Debt Update**: Sisa utang terupdate
- [ ] **Journal Entry**: Jurnal pelunasan tercatat
- [ ] **Validation**: Nominal ≤ Sisa utang
- [ ] **Payment Status**: Status pembelian terupdate jika lunas

---

## LAPORAN

### ✅ LAPORAN STOK
- [ ] **Display**: Tampil laporan stok dengan filter
- [ ] **Filter**: Filter by kategori dan tanggal berfungsi
- [ ] **Calculation**: Stok akhir = Stok awal + Masuk - Keluar
- [ ] **Value**: Nilai stok dihitung dengan benar
- [ ] **Export Excel**: Export ke Excel berhasil
- [ ] **Export PDF**: Export ke PDF berhasil

### ✅ LAPORAN PEMBELIAN
- [ ] **Display**: Tampil laporan pembelian dengan filter
- [ ] **Filter**: Filter by vendor, tanggal, status berfungsi
- [ ] **Detail**: Detail item pembelian tampil
- [ ] **Summary**: Total pembelian, dibayar, utang dihitung
- [ ] **Export Excel**: Export ke Excel berhasil
- [ ] **Export PDF**: Export ke PDF berhasil
- [ ] **Print Invoice**: Print invoice berhasil

### ✅ LAPORAN PENJUALAN
- [ ] **Display**: Tampil laporan penjualan dengan filter
- [ ] **Filter**: Filter by customer, tanggal, status berfungsi
- [ ] **Detail**: Detail item penjualan dengan HPP tampil
- [ ] **Summary**: Total penjualan, HPP, laba kotor, margin dihitung
- [ ] **Export Excel**: Export ke Excel berhasil
- [ ] **Export PDF**: Export ke PDF berhasil
- [ ] **Print Invoice**: Print invoice berhasil

### ✅ LAPORAN RETUR
- [ ] **Display**: Tampil laporan retur dengan filter
- [ ] **Filter**: Filter by tipe, tanggal, status berfungsi
- [ ] **Detail**: Detail item retur tampil
- [ ] **Export Excel**: Export ke Excel berhasil
- [ ] **Export PDF**: Export ke PDF berhasil

### ✅ LAPORAN PENGGAJIAN
- [ ] **Display**: Tampil laporan penggajian dengan filter
- [ ] **Filter**: Filter by periode, pegawai berfungsi
- [ ] **Detail**: Breakdown komponen gaji tampil
- [ ] **Summary**: Total gaji pokok, tunjangan, lembur, potongan dihitung
- [ ] **Export Excel**: Export ke Excel berhasil
- [ ] **Export PDF**: Export ke PDF berhasil
- [ ] **Print Slip**: Print slip gaji berhasil

### ✅ LAPORAN ALIRAN KAS
- [ ] **Display**: Tampil laporan aliran kas dengan filter
- [ ] **Filter**: Filter by tanggal berfungsi
- [ ] **Calculation**: Saldo awal, kas masuk, kas keluar, saldo akhir dihitung
- [ ] **Detail**: Detail transaksi kas tampil
- [ ] **Export Excel**: Export ke Excel berhasil
- [ ] **Export PDF**: Export ke PDF berhasil

### ✅ LAPORAN PENYUSUTAN ASET
- [ ] **Display**: Tampil laporan penyusutan dengan filter
- [ ] **Filter**: Filter by jenis, kategori, tahun berfungsi
- [ ] **Detail**: Jadwal penyusutan per bulan/tahun tampil
- [ ] **Posting**: Posting penyusutan bulanan berhasil
- [ ] **Journal Entry**: Jurnal penyusutan tercatat
- [ ] **Export Excel**: Export ke Excel berhasil
- [ ] **Export PDF**: Export ke PDF berhasil

---

## AKUNTANSI

### ✅ JURNAL UMUM
- [ ] **Display**: Tampil jurnal umum dengan filter
- [ ] **Filter**: Filter by tanggal, akun, referensi berfungsi
- [ ] **Auto Entry**: Jurnal dari transaksi tercatat otomatis
- [ ] **Manual Entry**: Input jurnal manual berhasil
- [ ] **Validation**: Total debit = Total kredit
- [ ] **Balance Check**: Jurnal balance

### ✅ BUKU BESAR
- [ ] **Display**: Tampil buku besar per akun
- [ ] **Filter**: Filter by akun, periode berfungsi
- [ ] **Running Balance**: Saldo running dihitung per transaksi
- [ ] **Detail**: Detail transaksi per akun tampil
- [ ] **Export**: Export berhasil

### ✅ NERACA SALDO
- [ ] **Display**: Tampil neraca saldo
- [ ] **Filter**: Filter by periode berfungsi
- [ ] **Balance**: Total debit = Total kredit
- [ ] **Calculation**: Saldo per akun dihitung dengan benar
- [ ] **Export**: Export berhasil

### ✅ LABA RUGI
- [ ] **Display**: Tampil laporan laba rugi
- [ ] **Filter**: Filter by periode berfungsi
- [ ] **Structure**: Struktur laporan sesuai (Pendapatan, HPP, Beban)
- [ ] **Calculation**: Laba kotor, laba operasional, laba bersih dihitung
- [ ] **Export**: Export berhasil

---

## INTEGRASI & VALIDASI

### ✅ MANAJEMEN STOK (FIFO)
- [ ] **Stock Layer**: Setiap pembelian membuat stock layer baru
- [ ] **FIFO Logic**: Penjualan/produksi mengambil dari stock layer tertua
- [ ] **Stock Movement**: Semua pergerakan stok tercatat
- [ ] **HPP Calculation**: HPP dihitung dari stock layer yang digunakan
- [ ] **Negative Stock**: Validasi stok tidak boleh negatif

### ✅ INTEGRASI AKUNTANSI
- [ ] **Auto Journal**: Semua transaksi otomatis membuat jurnal
- [ ] **Journal Lock**: Jurnal dari transaksi tidak bisa diedit manual
- [ ] **Reverse Journal**: Hapus transaksi reverse jurnal dengan benar
- [ ] **Real-time Posting**: Posting ke buku besar real-time
- [ ] **Balance Check**: Debit = Kredit di semua jurnal

### ✅ VALIDASI BISNIS
- [ ] **Transaction Lock**: Tidak boleh edit/hapus transaksi yang sudah posted
- [ ] **Master Data Lock**: Tidak boleh hapus master data yang sudah digunakan
- [ ] **Stock Validation**: Stok tidak boleh negatif
- [ ] **Date Validation**: Tanggal transaksi tidak boleh di masa depan
- [ ] **Balance Validation**: Total debit = Total kredit

### ✅ KEAMANAN
- [ ] **Authentication**: Semua route dilindungi middleware auth
- [ ] **Soft Delete**: Data penting menggunakan soft delete
- [ ] **Audit Trail**: Perubahan data tercatat (created_by, updated_by)
- [ ] **Authorization**: Role-based access control (jika ada)

---

## PERFORMANCE & UX

### ✅ PERFORMANCE
- [ ] **Page Load**: Halaman load < 3 detik
- [ ] **Query Optimization**: Query menggunakan eager loading
- [ ] **Pagination**: Pagination berfungsi di semua list
- [ ] **Search**: Search tidak timeout
- [ ] **Export**: Export tidak timeout untuk data besar

### ✅ USER EXPERIENCE
- [ ] **Responsive**: Tampilan responsive di mobile
- [ ] **Error Message**: Error message jelas dan informatif
- [ ] **Success Message**: Success message tampil setelah aksi berhasil
- [ ] **Loading Indicator**: Loading indicator tampil saat proses
- [ ] **Confirmation**: Konfirmasi sebelum hapus data
- [ ] **Breadcrumb**: Breadcrumb navigation jelas
- [ ] **Form Validation**: Validasi form real-time

---

## BUGS & ISSUES

### 🐛 KNOWN ISSUES
- [ ] Issue 1: [Deskripsi issue]
- [ ] Issue 2: [Deskripsi issue]

### ✅ FIXED ISSUES
- [x] Aset table structure mismatch - FIXED
- [x] Kode aset column missing - FIXED
- [x] Aset create form validation - FIXED

---

**Testing Date:** 10 November 2025  
**Tester:** [Nama Tester]  
**Version:** 1.0
