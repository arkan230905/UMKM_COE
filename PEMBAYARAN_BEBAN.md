# Panduan Pembayaran Beban Operasional

## Deskripsi
Fitur Pembayaran Beban digunakan untuk mencatat pembayaran beban operasional perusahaan seperti sewa, listrik, dan beban lainnya. Sistem ini terintegrasi dengan jurnal umum untuk pencatatan akuntansi otomatis.

## Alur Kerja

### 1. Persiapan Data Master
Sebelum membuat pembayaran beban, pastikan data master sudah siap:

#### a. Beban Operasional (Master Data)
- Buka: **Master Data → Beban Operasional**
- Pastikan beban yang akan dibayar sudah terdaftar (contoh: Beban Sewa, Beban Listrik)
- Setiap beban harus memiliki budget bulanan

#### b. COA (Chart of Accounts)
- Pastikan akun beban sudah ada di COA dengan kode diawali "5" (Expense)
- Contoh: 550 (BOP Listrik), 551 (BOP Sewa Tempat)
- Pastikan akun kas/bank ada: 111 (Kas Bank), 112 (Kas), 113 (Kas Kecil)

### 2. Membuat Pembayaran Beban

#### Langkah-langkah:
1. Buka: **Transaksi → Pembayaran Beban**
2. Klik tombol **"Tambah Pembayaran Beban"**
3. Isi form dengan data berikut:

| Field | Keterangan | Contoh |
|-------|-----------|--------|
| **Tanggal** | Tanggal pembayaran | 2026-04-25 |
| **Beban Operasional** | Pilih beban dari master data | Beban Listrik |
| **Budget Bulanan** | Otomatis muncul dari master data | Rp 500.000 |
| **Nominal Pembayaran** | Jumlah yang dibayarkan (bisa berbeda dengan budget) | Rp 450.000 |
| **Akun Beban** | Pilih akun beban dari COA | 550 - BOP Listrik |
| **Catatan** | Keterangan tambahan (opsional) | Pembayaran listrik bulan April |

4. Klik **"Simpan"**

### 3. Sistem Otomatis

Ketika pembayaran beban disimpan, sistem akan:

1. **Menyimpan data pembayaran** ke tabel `pembayaran_beban`
2. **Membuat jurnal otomatis** dengan 2 baris:
   - **DEBIT**: Akun Beban (contoh: 550 - BOP Listrik) = Rp 450.000
   - **KREDIT**: Akun Kas Bank (111) = Rp 450.000
3. **Memperbarui saldo kas** secara real-time

### 4. Contoh Jurnal yang Dibuat

```
Tanggal: 2026-04-25
Keterangan: Pembayaran Beban: Pembayaran listrik bulan April

Debit:
  550 - BOP Listrik                    Rp 450.000

Kredit:
  111 - Kas Bank                                    Rp 450.000
```

## Fitur-Fitur

### Daftar Pembayaran Beban
- Lihat semua pembayaran beban yang sudah dibuat
- Filter berdasarkan:
  - Tanggal (dari - sampai)
  - Beban Operasional
  - Akun Beban
  - Akun Kas/Bank

### Detail Pembayaran
- Klik pada baris pembayaran untuk melihat detail
- Lihat jurnal yang dibuat otomatis
- Lihat informasi pembuat dan waktu pembuatan

### Hapus Pembayaran
- Klik tombol **"Hapus"** untuk menghapus pembayaran
- Sistem akan otomatis menghapus jurnal yang terkait
- Saldo kas akan dikembalikan

## Validasi & Error Handling

### Error: "Gagal menyimpan pembayaran beban"
**Penyebab:**
- Akun beban tidak ditemukan di COA
- Akun kas (111) tidak ditemukan
- Data beban operasional tidak valid

**Solusi:**
1. Pastikan akun beban ada di COA dengan kode diawali "5"
2. Pastikan akun kas (111) ada di COA
3. Pastikan beban operasional sudah terdaftar di master data

### Error: "Beban operasional tidak valid"
**Penyebab:**
- Beban operasional yang dipilih tidak ada di database

**Solusi:**
1. Buka Master Data → Beban Operasional
2. Tambahkan beban operasional yang diperlukan
3. Coba lagi

### Error: "Akun beban tidak valid"
**Penyebab:**
- Akun beban yang dipilih tidak ada di COA

**Solusi:**
1. Buka Master Data → COA
2. Tambahkan akun beban dengan kode diawali "5"
3. Coba lagi

## Integrasi dengan Laporan

### Laporan Kas & Bank
- Pembayaran beban akan muncul sebagai pengeluaran kas
- Saldo kas akan berkurang sesuai nominal pembayaran

### Laporan Beban Operasional
- Pembayaran beban akan dibandingkan dengan budget
- Menampilkan variance (selisih antara budget dan aktual)

### Jurnal Umum
- Pembayaran beban akan muncul di jurnal umum
- Dapat dilihat dengan filter referensi "PB-{ID}"

## Tips & Best Practices

1. **Gunakan tanggal yang tepat** - Pastikan tanggal pembayaran sesuai dengan tanggal transaksi aktual
2. **Isi catatan dengan jelas** - Catatan membantu tracking dan audit
3. **Periksa nominal pembayaran** - Nominal bisa berbeda dengan budget, pastikan sesuai dengan bukti pembayaran
4. **Gunakan akun beban yang spesifik** - Jangan gunakan akun beban umum, gunakan akun yang paling sesuai
5. **Backup data berkala** - Lakukan backup database secara berkala

## Troubleshooting

### Pembayaran tidak muncul di laporan
- Pastikan tanggal pembayaran dalam range periode laporan
- Periksa filter yang digunakan di laporan
- Pastikan pembayaran sudah disimpan dengan status "Berhasil"

### Saldo kas tidak sesuai
- Periksa apakah ada pembayaran beban yang belum diposting ke jurnal
- Lihat jurnal umum untuk memastikan semua transaksi tercatat
- Jalankan validasi saldo kas

### Tidak bisa menghapus pembayaran
- Pembayaran mungkin sudah digunakan dalam laporan
- Coba hapus dari tanggal paling baru terlebih dahulu
- Hubungi administrator jika masalah persisten

## Database Schema

### Tabel: pembayaran_beban
```sql
CREATE TABLE pembayaran_beban (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    keterangan VARCHAR(255) NOT NULL,
    akun_beban_id BIGINT NOT NULL,
    akun_kas_id BIGINT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    user_id BIGINT,
    catatan TEXT,
    beban_operasional_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (akun_beban_id) REFERENCES coas(id),
    FOREIGN KEY (akun_kas_id) REFERENCES coas(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (beban_operasional_id) REFERENCES beban_operasional(id)
);
```

## Hubungan dengan Tabel Lain

- **coas**: Menyimpan akun beban dan akun kas
- **beban_operasional**: Menyimpan master data beban
- **jurnal**: Menyimpan jurnal transaksi pembayaran beban
- **users**: Menyimpan informasi user yang membuat pembayaran

---

**Last Updated:** 2026-04-20  
**Version:** 1.0  
**Maintainer:** System Administrator
